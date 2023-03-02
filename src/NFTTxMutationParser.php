<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser;

/**
 * NFT Transaction Mutation Parser
 */
class NFTTxMutationParser
{
  private readonly string $account;
  private readonly \stdClass $tx;
  private array $result_in = [];
  private array $result_out = [];

  public function __construct(string $reference_account, \stdClass $tx)
  {
    $this->account = $reference_account;
    $this->tx = $tx;

    $meta = $this->tx->meta;

    if(!isset($meta->AffectedNodes)) {
      return;
    }

    $affected_account = $this->tx->Account;
    if($this->tx->TransactionType = 'NFTokenMint' && isset($this->tx->Issuer)) {
      $affected_account = $this->tx->Issuer;
    }

    if($affected_account != $this->account) {
      return;
    }
    //dd($affected_account);

    
    $in = $out = [];
    
    foreach($meta->AffectedNodes as $affected_node) {

      if(isset($affected_node->ModifiedNode)) {
        if($affected_node->ModifiedNode->LedgerEntryType === 'NFTokenPage') {
          $inout = $this->extractNFTokenIDsFromNFTTokenPageChange(
            $affected_node->ModifiedNode->PreviousFields,
            $affected_node->ModifiedNode->FinalFields
          );
          $in = \array_merge($in,$inout['in']);
          $out = \array_merge($out,$inout['out']);
        }
      }
    }

    $in = \array_unique($in);
    $out = \array_unique($out);
    //dd($in,$out,'exit');
    $this->result_in = $in;
    $this->result_out = $out;
  }

  /**
   * @return array ['in' => [ 'NFTokenID, ... ], 'out' => [ 'NFTokenID, ... ] ]
   */
  private function extractNFTokenIDsFromNFTTokenPageChange($PreviousFields, $FinalFields): array
  {
    $in = $out = [];
    $prev_tokens  = isset($PreviousFields->NFTokens) ? $PreviousFields->NFTokens : [];
    $final_tokens = isset($PreviousFields->NFTokens) ? $FinalFields->NFTokens : [];

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

  public function result(): array
  {
    return [
      'in' => $this->result_in,
      'out' => $this->result_out
    ];
  }
}
