<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser;

/**
 * NFT Transaction Mutation Parser
 */
class NFTTxMutationParser
{
  private readonly string $account;
  private readonly \stdClass $tx;
  private ?string $affected_account = null;
  private ?string $affected_account_context = null;
  private ?string $result_nftokenid = null;
  private ?bool   $result_in = null;

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
    
    if($this->affected_account != $this->account)
      return;

    $this->result_nftokenid = $this->extractAffectedNFTokenID();
    dd('test');
  }

  private function handleNFTokenBurn(): void
  {
    $affected_account = $this->tx->Account;
    if(isset($this->tx->Owner))
      $affected_account = $this->tx->Owner;
    
    if($this->affected_account != $this->account)
      return;
  }

  private function handleNFTokenAcceptOffer(): void
  {
    dd('todo');
  }


  private function extractAffectedNFTokenID(): string
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
      'nftokenid' => $this->result_nftokenid,
      'direction' => ($this->result_in === null) ? 'UNKNOWN': ( $this->result_in ? 'IN':'OUT' )
    ];
  }
}
