<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser;

/**
 * NFT Transaction Mutation Parser
 */
class NFTTxMutationParser
{
  private readonly string $account;
  private readonly \stdClass $tx;
  private string $affected_account;
  private array $result_in = [];
  private array $result_out = [];

  public function __construct(string $reference_account, \stdClass $tx)
  {
    $this->account = $reference_account;
    $this->tx = $tx;
    $this->affected_account = $this->tx->Account;
    
    $meta = $this->tx->meta;

    if(!isset($meta->AffectedNodes)) {
      return;
    }

    # Switch affected account to Issuer in case minting in behalf to
    if($this->tx->TransactionType = 'NFTokenMint' && isset($this->tx->Issuer)) {
      $this->affected_account = $this->tx->Issuer;
    }

    # Switch affected account to Owner in case burning in behalf to
    if($this->tx->TransactionType = 'NFTokenBurn' && isset($this->tx->Owner)) {
      $this->affected_account = $this->tx->Owner;
    }
   
    # If affected account is not context account exit
    if($this->affected_account != $this->account) {
      return;
    }
   
    $in = $out = [];
    
    foreach($meta->AffectedNodes as $affected_node) {

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

      } elseif(isset($affected_node->ModifiedNode)) {

        if($affected_node->ModifiedNode->LedgerEntryType === 'NFTokenPage') {
          $inout = $this->extractNFTokenIDsFromNFTTokenPageChange(
            $affected_node->ModifiedNode->PreviousFields,
            $affected_node->ModifiedNode->FinalFields
          );
          $in = \array_merge($in,$inout['in']);
          $out = \array_merge($out,$inout['out']);
          unset($inout);
        }

      } elseif(isset($affected_node->DeletedNode)) {

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

    $this->result_in = $in;
    $this->result_out = $out;
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

  public function result(): array
  {
    return [
      'in' => $this->result_in,
      'out' => $this->result_out
    ];
  }
}
