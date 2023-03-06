<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser;

/**
 * NFT Transaction Mutation Parser
 */
class NFTTxMutationParser
{
  const DIRECTION_UNKNOWN = 'UNKNOWN';
  const DIRECTION_IN = 'IN';
  const DIRECTION_OUT = 'OUT';


  private readonly string $account;
  private readonly \stdClass $tx;
  private ?string $result_nftokenid = null;
  private string $result_direction = self::DIRECTION_UNKNOWN;

  public function __construct(string $reference_account, \stdClass $tx)
  {
    # COMMON
    $this->account = $reference_account;
    $this->tx = $tx;

    if(!isset($this->tx->meta->AffectedNodes))
      return;

    switch($this->tx->TransactionType) {
      case 'NFTokenMint':
        $this->handleNFTokenMint();
        break;
      case 'NFTokenBurn':
        $this->handleNFTokenBurn();
        break;
      case 'NFTokenAcceptOffer':
        $this->handleNFTokenAcceptOffer();
        break;
    }
  }

  private function handleNFTokenMint(): void
  {
    $affected_account = $this->tx->Account;
    if(isset($this->tx->Issuer))
      $affected_account = $this->tx->Issuer;
    
    # If affected account is not context account exit
    if($affected_account != $this->account)
      return;

    $this->result_direction = self::DIRECTION_IN;
    
    $this->result_nftokenid = $this->extractAffectedNFTokenID();
    //dd($this->result_nftokenid);
  }

  private function handleNFTokenBurn(): void
  {
    $affected_account = $this->tx->Account;
    if(isset($this->tx->Owner))
      $affected_account = $this->tx->Owner;
    
    //Reference account is not affected account
    if($affected_account != $this->account)
      return;

    $this->result_direction = self::DIRECTION_OUT;

    $this->result_nftokenid = $this->extractAffectedNFTokenID();
  }

  private function handleNFTokenAcceptOffer(): void
  {
    $affected_account = null;
    $context = null;
    //Affected accounts can be either buyer or seller, in both Direct or Brokered mode.
    if(isset($this->tx->NFTokenBuyOffer) && !isset($this->tx->NFTokenSellOffer)) { //DIRECT
      //This is buy offer, Account has created NFTokenBuyOffer so Account is seller
      $context = 'SELLER';
      $affected_account = $this->tx->Account;
    } elseif(!isset($this->tx->NFTokenBuyOffer) && isset($this->tx->NFTokenSellOffer)) { //DIRECT
      //This is buy offer, Account has created NFTokenSellOffer so Account is buyer
      $context = 'BUYER';
      $affected_account = $this->tx->Account;
    } elseif(isset($this->tx->NFTokenBuyOffer) && isset($this->tx->NFTokenSellOffer)) { //BROKERED
      $context = 'BROKER';
      $affected_account = null;
    } else {
      throw new \Exception('Not implemented case in handleNFTokenAcceptOffer');
    }

    //Extract NFT in question (seller or buyer context only)
    //Extracted NFTokenID is always same in all offers in meta
    $data = $this->extractDataFromDeletedOfferInMeta();
    $NFTokenID = $data['NFTokenID'];

    # Perpective flipping in case when reference account is not initiator of NFTokenAcceptOffer
    if($affected_account != $this->account) {

      //If this is a buy offer, extracted account is BUYER
      if($context == 'SELLER') {
          $affected_account = $data['account'];
          $context = 'BUYER'; //flip perspective
      } elseif($context == 'BUYER') {
          $affected_account = $data['account'];
          $context = 'SELLER'; //flip perspective
      } elseif ($context == 'BROKER') {
        //We have two offers, sell offer and buy offer, extract appropriate
        $data = $this->extractDataFromDeletedOfferInMeta_PriorityReferenceAccount();
        if($this->tx->NFTokenBuyOffer == $data['LedgerIndex']) {
          $context = 'BUYER';
        } elseif($this->tx->NFTokenSellOffer == $data['LedgerIndex']) {
          $context = 'SELLER';
        }
        $affected_account = $data['account'];
      }
    }
    
    //Broker or other unrelated - exit
    if($affected_account === null)
      return;

    //Reference account is not affected account
    if($affected_account != $this->account)
      return;

    $this->result_nftokenid = $NFTokenID;

    if($context == 'SELLER') {
      $this->result_direction = self::DIRECTION_OUT;
    } elseif($context == 'BUYER') {
      $this->result_direction = self::DIRECTION_IN;
    }
  }

  /**
   * OK
   */
  private function extractAffectedNFTokenID(): string
  {
    $in = $out = [];
    foreach($this->tx->meta->AffectedNodes as $affected_node) {

      if(isset($affected_node->CreatedNode)) {

        if($affected_node->CreatedNode->LedgerEntryType === 'NFTokenPage') {

          $inout = $this->extractNFTokenIDsFromNFTTokenPageChange(
            null,
            $affected_node->CreatedNode->NewFields
          );
          $in = \array_merge($in,$inout['in']);
          $out = \array_merge($out,$inout['out']);
          unset($inout);
          
        }
      }

      if(isset($affected_node->ModifiedNode)) {

        if($affected_node->ModifiedNode->LedgerEntryType === 'NFTokenPage') {

          $inout = $this->extractNFTokenIDsFromNFTTokenPageChange(
            $affected_node->ModifiedNode->PreviousFields,
            $affected_node->ModifiedNode->FinalFields
          );
          $in = \array_merge($in,$inout['in']);
          $out = \array_merge($out,$inout['out']);
          unset($inout);
        }

      }
      
      if(isset($affected_node->DeletedNode)) {

        if($affected_node->DeletedNode->LedgerEntryType === 'NFTokenPage') {
          $inout = $this->extractNFTokenIDsFromNFTTokenPageChange(
            $affected_node->DeletedNode->FinalFields,
            null
          );
          $in = \array_merge($in,$inout['in']);
          $out = \array_merge($out,$inout['out']);
          unset($inout);
        }

      }
    }
    
    $in = \array_unique($in);
    $out = \array_unique($out);

    $merged = \array_merge($in,$out);

    if(count($merged) == 1)
      return $merged[0];
      
    if(count($merged) > 1)
      throw new \Exception('Unahdled multiple token changes in NFTTokenPage meta detected');
    
    if(count($merged) < 1)
      throw new \Exception('Unahdled no token changes in NFTTokenPage meta detected');

  }

  /**
   * Helper function that extracts token changes from single meta prev and final fields.
   * @return array ['in' => [ 'NFTokenID, ... ], 'out' => [ 'NFTokenID, ... ] ]
   * OK
   */
  private function extractNFTokenIDsFromNFTTokenPageChange(?\stdClass $PreviousFields, ?\stdClass $FinalFields): array
  {
    $in = $out = [];
    $prev_tokens  = ($PreviousFields !== null && isset($PreviousFields->NFTokens)) ? $PreviousFields->NFTokens : [];
    $final_tokens = ($FinalFields    !== null && isset($FinalFields->NFTokens))    ? $FinalFields->NFTokens : [];

    $prev = [];
    foreach($prev_tokens as $pt) {
      $prev[] = $pt->NFToken->NFTokenID;
    }

    $final = [];
    foreach($final_tokens as $ft) {
      $final[] = $ft->NFToken->NFTokenID;
    }

    $in = \array_diff($final,$prev);
    $out = \array_diff($prev,$final);

    return ['in' => \array_values($in), 'out' => \array_values($out)];
  }

  private function extractDataFromDeletedOfferInMeta()
  {
    $account = null;
    $NFTokenID = null;

    foreach($this->tx->meta->AffectedNodes as $affected_node) {

      if(isset($affected_node->DeletedNode) && $affected_node->DeletedNode->LedgerEntryType == 'NFTokenOffer') {
        $node = $affected_node->DeletedNode;
        
        //if(isset($node->FinalFields->Owner))
        $account = $node->FinalFields->Owner;
        $NFTokenID = $node->FinalFields->NFTokenID;
        break;
      }
    }

    return ['account' => $account, 'NFTokenID' => $NFTokenID];
  }

  private function extractDataFromDeletedOfferInMeta_PriorityReferenceAccount()
  {
    $account = $NFTokenID = $LedgerIndex = null;

    foreach($this->tx->meta->AffectedNodes as $affected_node) {

      if(isset($affected_node->DeletedNode) && $affected_node->DeletedNode->LedgerEntryType == 'NFTokenOffer') {
        $node = $affected_node->DeletedNode;
        if($node->FinalFields->Owner == $this->account) {
          $account = $node->FinalFields->Owner;
          $NFTokenID = $node->FinalFields->NFTokenID;
          $LedgerIndex = $node->LedgerIndex;
          break;
        }
      }
    }

    return ['account' => $account, 'NFTokenID' => $NFTokenID, 'LedgerIndex' => $LedgerIndex];
  }

  public function result(): array
  {
    return [
      'nftokenid' => $this->result_nftokenid,
      'direction' => $this->result_direction,
      
    ];
  }
}
