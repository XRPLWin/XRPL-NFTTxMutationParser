<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser;

/**
 * NFT Transaction Mutation Parser
 */
class NFTTxMutationParserOld
{
  private readonly string $account;
  private readonly \stdClass $tx;
  private ?string $affected_account = null;
  private ?string $affected_account_context = null;
  private array $result_in = [];
  private array $result_out = [];

  public function __construct(string $reference_account, \stdClass $tx)
  {
    # COMMON
    $this->account = $reference_account;
    $this->tx = $tx;
    $this->affected_account = $this->tx->Account;

    if(!isset($this->tx->meta->AffectedNodes)) {
      return;
    }

    # Switch affected account to Issuer in case minting in behalf to
    if($this->tx->TransactionType == 'NFTokenMint' && isset($this->tx->Issuer)) {
      $this->affected_account = $this->tx->Issuer;
    }

    # Switch affected account to Owner in case burning in behalf to
    if($this->tx->TransactionType == 'NFTokenBurn' && isset($this->tx->Owner)) {
      $this->affected_account = $this->tx->Owner;
    }

    if($this->tx->TransactionType == 'NFTokenAcceptOffer') {
      $this->affected_account = null;

      //Affected accounts can be either buyer or seller, in both Direct or Brokered mode.
      if(isset($this->tx->NFTokenBuyOffer) && !isset($this->tx->NFTokenSellOffer)) { //DIRECT
        //This is buy offer, Account has created NFTokenBuyOffer so Account is seller
        $this->affected_account_context = 'SELLER';
        $this->affected_account = $this->tx->Account;
      } elseif(!isset($this->tx->NFTokenBuyOffer) && isset($this->tx->NFTokenSellOffer)) { //DIRECT
        //This is buy offer, Account has created NFTokenSellOffer so Account is buyer
        $this->affected_account_context = 'BUYER';
        $this->affected_account = $this->tx->Account;
      } elseif(!isset($this->tx->NFTokenBuyOffer) && isset($this->tx->NFTokenSellOffer)) { //BROKERED
        $this->affected_account_context = 'BROKER';
        $this->affected_account = null;
      } else {
        //dd($this);
        throw new \Exception('Not implemented case');
      }

      if($this->affected_account != $this->account) {
        //Referenced account is not initiator, search for account in metadata
        
        if($this->affected_account_context == 'SELLER') {
          $this->affected_account_context = 'BUYER';
          $this->affected_account = $this->extractAffectedAccountFromDeletedOfferInMeta();
        } else if($this->affected_account_context == 'BUYER') {
          $this->affected_account_context = 'SELLER';
          $this->affected_account = $this->extractAffectedAccountFromDeletedOfferInMeta();
        }
        else {
          //we will have possibly two deleted Nft offers here...
          throw new \Exception('Not implemented when brokered');
        }
        //dd($this->affected_account_context);
      }
    }

    # No affected account detected in reference account context
    if($this->affected_account === null) {
      return;
    }
   
    # If affected account is not context account exit
    if($this->affected_account != $this->account) {
      return;
    }
    
    if($this->tx->TransactionType == 'NFTokenAcceptOffer') {
      $in_out = ['in' => [$this->handleOfferTransaction_ExtractTokenID()],'out' => []];
    }
    else
      $in_out = $this->handleSimpleTransaction();

    $in = $in_out['in'];
    $out = $in_out['out'];

    if($this->affected_account_context !== null) {
      //context exists
      $nftoken_id = $this->extractSingleMovedToken($in,$out);
      //dd($nftoken_id);
      if($nftoken_id === null)
        throw new \Exception('Multi token movement detected');
      
      //Set default values
      $this->result_in = [];
      $this->result_out = [];

      if($this->affected_account_context === 'SELLER')
        $this->result_out = [$nftoken_id];
      elseif($this->affected_account_context === 'BUYER')
        $this->result_in = [$nftoken_id];
      
    } else {
      $this->result_in = $in;
      $this->result_out = $out;
    }
  }

  private function handleOfferTransaction_ExtractTokenID(): string
  {
    $NFTokenID = null;
    //Extract single NFTokenID from deleted offer node
    foreach($this->tx->meta->AffectedNodes as $affected_node) {

      if(isset($affected_node->DeletedNode) && $affected_node->DeletedNode->LedgerEntryType == 'NFTokenOffer') {
        $node = $affected_node->DeletedNode;
        
        $NFTokenID = $node->FinalFields->NFTokenID;
        break;
      }
    }

    if($NFTokenID !== null)
      return $NFTokenID;

    throw new \Exception('Unable to extract NFTokenID from deleted node of type NFTokenOffer in method handleOfferTransaction');
  }

  private function handleSimpleTransaction()
  {
    $in = $out = [];
    //$NFTokenPageIndex = 0;
    foreach($this->tx->meta->AffectedNodes as $affected_node) {

      if(isset($affected_node->CreatedNode)) {

        if($affected_node->CreatedNode->LedgerEntryType === 'NFTokenPage') {

          //$NFTokenPageIndex++;
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

          //$NFTokenPageIndex++;
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

          //$NFTokenPageIndex++;
          $inout = $this->extractNFTokenIDsFromNFTTokenPageChange(
            $affected_node->DeletedNode->FinalFields,
            null
          );
          $in = \array_merge($in,$inout['in']);
          $out = \array_merge($out,$inout['out']);
          unset($inout);
          //$NFTokenPageIndex++;
        }

      }
    }
    
    $in = \array_unique($in);
    $out = \array_unique($out);
    return ['in' => $in, 'out' => $out];
  }

  /**
   * @return array ['in' => [ 'NFTokenID, ... ], 'out' => [ 'NFTokenID, ... ] ]
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

  private function extractSingleMovedToken(array $in, array $out): ?string
  {
    $r = \array_unique(\array_merge($in,$out));
    if(count($r) > 0)
      return $r[0];
    return null;
  }

  private function extractAffectedAccountFromDeletedOfferInMeta()
  {
    foreach($this->tx->meta->AffectedNodes as $affected_node) {

      if(isset($affected_node->DeletedNode) && $affected_node->DeletedNode->LedgerEntryType == 'NFTokenOffer') {
        $node = $affected_node->DeletedNode;
        
        return $node->FinalFields->Owner;
      }

      /*$node = null;
      if(isset($affected_node->CreatedNode)) {
        $node = $affected_node->CreatedNode;
      } elseif(isset($affected_node->ModifiedNode)) {
        $node = $affected_node->ModifiedNode;
      } elseif(isset($affected_node->DeletedNode)) {
        $node = $affected_node->DeletedNode;
      }
      if($node === null)
        continue;
      if($node->LedgerEntryType == 'NFTokenOffer') {

      }
      
      dd($node);*/
    }

    throw new \Exception('Unable to extract account from deleted node of type NFTokenOffer in method extractAffectedAccountFromDeletedOfferInMeta');
  }

  public function result(): array
  {
    return [
      'in' => $this->result_in,
      'out' => $this->result_out
    ];
  }
}
