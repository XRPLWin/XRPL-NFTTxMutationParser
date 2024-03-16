<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser;

/**
 * NFT Transaction Mutation Parser
 */
class NFTTxMutationParser
{
  const DIRECTION_UNKNOWN = 'UNKNOWN';
  const DIRECTION_IN      = 'IN';
  const DIRECTION_OUT     = 'OUT';

  # Common
  const ROLE_UNKNOWN    = 'UNKNOWN';
  const ROLE_OWNER      = 'OWNER';
  # Mint (minter, owner, unknown)
  const ROLE_MINTER     = 'MINTER';
  # Burn (burner, owner, unknown)
  const ROLE_BURNER     = 'BURNER';
  # Trade (buyer, seller, broker, issuer, owner (new owner), unknown)
  const ROLE_BUYER      = 'BUYER';
  const ROLE_SELLER     = 'SELLER';
  const ROLE_BROKER     = 'BROKER';
  const ROLE_ISSUER     = 'ISSUER';

  # Context
  const CONTEXT_OFFER_BUY  = 'BUY';
  const CONTEXT_OFFER_SELL = 'SELL';
  const CONTEXT_OFFER_BROKERED = 'BROKERED';

  private readonly string $account;
  private readonly \stdClass $tx;

  # Result variables
  private ?string $nft = null;
  private array $nfts = []; //Remit transfers
  private ?string $context = null;

  # Reference account result variables
  private ?string $ref_nft = null;
  private array $ref_nfts = []; //Remit transfers
  private string $ref_direction = self::DIRECTION_UNKNOWN;
  private array $ref_roles = [];

  public function __construct(string $reference_account, \stdClass $tx)
  {
    # COMMON
    $this->account = $reference_account;
    $this->tx = $tx;

    /**
     * Some transaction types like 'ledger' send "metaData" instead of "meta"
     * Reference metaData to meta if meta does not exist.
     */
    if(!isset($this->tx->meta) && isset($this->tx->metaData))
      $this->tx->meta = $this->tx->metaData;

    if(!isset($this->tx->meta))
      throw new \Exception('meta or metaData not found in sent transaction');

    if(!isset($this->tx->meta->AffectedNodes))
      return;

    switch($this->tx->TransactionType) {
      case 'NFTokenMint':
        $this->handleNFTokenMint();
        break;
      case 'NFTokenBurn':
        $this->handleNFTokenBurn();
        break;
      case 'NFTokenCreateOffer':
        $this->handleNFTokenCreateOffer();
        break;
      case 'NFTokenAcceptOffer':
        $this->handleNFTokenAcceptOffer();
        break;
      case 'URITokenBuy':
        $this->handleURITokenBuy();
        break;
      case 'URITokenMint':
        $this->handleURITokenMint();
        break;
      case 'URITokenBurn':
        $this->handleURITokenBurn();
        break;
      case 'URITokenCreateSellOffer':
        $this->handleURITokenCreateSellOffer();
        break;
      case 'URITokenCancelSellOffer':
        $this->handleURITokenCancelSellOffer();
        break;
      case 'Remit':
        $this->handleRemit();
        break;
    }
    $this->nft = $this->ref_nft;
    
    if($this->nft === null) {

      // Extract subject NFTokenID:
      
      switch($this->tx->TransactionType) {
        case 'NFTokenMint':
          $this->nft = $this->extractAffectedNFTokenID();
          break;
        case 'NFTokenBurn':
          $this->nft = $this->tx->NFTokenID;
          //$this->nft = $this->extractAffectedNFTokenID();
          break;
        case 'NFTokenAcceptOffer':
          $this->nft = $this->extractDataFromDeletedOfferInMeta()['NFTokenID'];
          break;
        case 'NFTokenCreateOffer':
          $this->nft = $this->tx->NFTokenID;
          break;
        case 'URITokenBuy':
        case 'URITokenMint':
        case 'URITokenBurn':
        case 'URITokenCreateSellOffer':
        case 'URITokenCancelSellOffer':
          $this->nft = $this->extractAffectedURITokenID();
          break;
        case 'Remit':
          $this->nft = $this->extractAffectedURITokenID(false);
          $this->nfts = $this->extractRemitURITokenIDs();
          break;
      }
    }
  }

  private function handleNFTokenMint(): void
  {
    $affected_account = $this->tx->Account;
    
    if(isset($this->tx->Issuer))
      $affected_account = $this->tx->Issuer;
    
    # ROLE START
    if($this->account == $this->tx->Account)
      $this->ref_roles[] = self::ROLE_MINTER;
        
    if(isset($this->tx->Issuer)) {
      if($this->account == $this->tx->Issuer)
        $this->ref_roles[] = self::ROLE_OWNER;
    } else {
      if($this->account == $this->tx->Account)
        $this->ref_roles[] = self::ROLE_OWNER;
    }
    # ROLE END
    
    # If affected account is not context account exit
    if($affected_account != $this->account)
      return;

    $this->ref_direction = self::DIRECTION_IN;
    $this->ref_nft = $this->extractAffectedNFTokenID();

  }

  private function handleNFTokenBurn(): void
  {
    $affected_account = $this->tx->Account;

    if(isset($this->tx->Owner))
      $affected_account = $this->tx->Owner;

    # ROLE START
    if($this->account == $this->tx->Account)
        $this->ref_roles[] = self::ROLE_BURNER;

    if(isset($this->tx->Owner)) {
      if($this->account == $this->tx->Owner)
        $this->ref_roles[] = self::ROLE_OWNER;
    } else {
      if($this->account == $this->tx->Account)
        $this->ref_roles[] = self::ROLE_OWNER;
    }
    # ROLE END
    
    //Reference account is not affected account
    if($affected_account != $this->account)
      return;

    $this->ref_direction = self::DIRECTION_OUT;

    $this->ref_nft = $this->tx->NFTokenID;
    //$this->ref_nft = $this->extractAffectedNFTokenID();
  }

  private function handleNFTokenCreateOffer(): void
  {
    $this->context = self::CONTEXT_OFFER_BUY;

    if(isset($this->tx->Flags) &&  $this->tx->Flags == 1) {
      $this->context = self::CONTEXT_OFFER_SELL;
    }

    if(isset($this->tx->Owner) && $this->account == $this->tx->Owner) {
      $this->ref_roles[] = self::ROLE_OWNER;
    }

    if(!isset($this->tx->Owner) && $this->account == $this->tx->Account) {
      $this->ref_roles[] = self::ROLE_OWNER;
    }

    //$this->context = self::CONTEXT_OFFER_SELL;
    //if(isset($this->tx->Owner)) {
    //  if($this->tx->Owner != $this->tx->Account) {
    //    $this->context = self::CONTEXT_OFFER_BUY;
    //  }
    //}
  }

  private function handleNFTokenAcceptOffer(): void
  {
    $affected_account = null;
    $context = null;
    //Affected accounts can be either buyer or seller, in both Direct or Brokered mode.
    if(isset($this->tx->NFTokenBuyOffer) && !isset($this->tx->NFTokenSellOffer)) { //DIRECT
      //This is buy offer, Account has created NFTokenBuyOffer so Account is seller
      $context = 'SELLER';
      $this->context = self::CONTEXT_OFFER_BUY;
      $affected_account = $this->tx->Account;
    } elseif(!isset($this->tx->NFTokenBuyOffer) && isset($this->tx->NFTokenSellOffer)) { //DIRECT
      //This is buy offer, Account has created NFTokenSellOffer so Account is buyer
      $context = 'BUYER';
      $affected_account = $this->tx->Account;
      $this->context = self::CONTEXT_OFFER_SELL;
      if($this->account == $affected_account)
        $this->ref_roles = [self::ROLE_OWNER];
    } elseif(isset($this->tx->NFTokenBuyOffer) && isset($this->tx->NFTokenSellOffer)) { //BROKERED
      $context = 'BROKER';
      $this->context = self::CONTEXT_OFFER_BROKERED;
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
          if($this->account == $affected_account)
            $this->ref_roles = [self::ROLE_OWNER];
      } elseif($context == 'BUYER') {
          $affected_account = $data['account'];
          $context = 'SELLER'; //flip perspective
      } elseif ($context == 'BROKER') {
        
        //We have two offers, sell offer and buy offer, extract appropriate
        $data = $this->extractDataFromDeletedOfferInMeta_PriorityReferenceAccount();
        
        if($this->tx->NFTokenBuyOffer == $data['LedgerIndex']) {
          $affected_account = $data['account'];
          $context = 'BUYER';
          if($this->account == $affected_account)
            $this->ref_roles = [self::ROLE_OWNER];
        } elseif($this->tx->NFTokenSellOffer == $data['LedgerIndex']) {
          $affected_account = $data['account'];
          $context = 'SELLER';
        } else {
          if($this->account == $this->tx->Account)
            $this->ref_roles = [self::ROLE_BROKER];
        }
      }
    }

    //Check if perspective account is issuer of token (checked by balance changes)
    if($affected_account === null && count($this->ref_roles) == 0) {
      foreach($this->tx->meta->AffectedNodes as $an) {
        if(isset($an->ModifiedNode) && $an->ModifiedNode->LedgerEntryType == 'AccountRoot') {
          if(isset($an->ModifiedNode->PreviousFields->Balance) && isset($an->ModifiedNode->FinalFields->Balance)) {
           
            if((string)$an->ModifiedNode->PreviousFields->Balance !== (string)$an->ModifiedNode->FinalFields->Balance) {
              if($an->ModifiedNode->FinalFields->Account == $this->account) {
                $this->ref_roles = [self::ROLE_ISSUER];
              }
            }
          }
        }
      }
    }
    
    //Broker or other unrelated - exit
    if($affected_account === null)
      return;

    //Reference account is not affected account
    if($affected_account != $this->account)
      return;

    $this->ref_nft = $NFTokenID;

    if($context == 'SELLER') {
      $this->ref_direction = self::DIRECTION_OUT;
      $this->ref_roles[] = self::ROLE_SELLER;
    } elseif($context == 'BUYER') {
      $this->ref_direction = self::DIRECTION_IN;
      $this->ref_roles[] = self::ROLE_BUYER;
    }
  }

  /**
   * Buys a token by consuming URIToken offer
   * @return void
   */
  private function handleURITokenBuy(): void
  {
    if($this->account == $this->tx->Account) {
      $this->ref_nft = $this->tx->URITokenID;
      $this->ref_direction = self::DIRECTION_IN;
      $this->ref_roles = [self::ROLE_OWNER,self::ROLE_BUYER];
      return;
    }

    //check if $this->account is seller from metadata
    foreach($this->tx->meta->AffectedNodes as $an) {
      if(isset($an->ModifiedNode) && $an->ModifiedNode->LedgerEntryType == 'URIToken') {
        if(isset($an->ModifiedNode->PreviousFields->Owner) && isset($an->ModifiedNode->FinalFields->Owner)) {
          if((string)$an->ModifiedNode->PreviousFields->Owner !== (string)$an->ModifiedNode->FinalFields->Owner) {
            if($an->ModifiedNode->PreviousFields->Owner == $this->account) {
              $this->ref_nft = $this->tx->URITokenID;
              $this->ref_direction = self::DIRECTION_OUT;
              $this->ref_roles = [self::ROLE_SELLER];
              return;
            }
          }
        }
      }
    }
  }

  /**
   * Mints new token, minted tokens always first go to minter.
   * @return void
   */
  private function handleURITokenMint(): void
  {
    if($this->account == $this->tx->Account) {
      //minter perspective
      if(isset($this->tx->Destination)) {
        //other owner
        $this->ref_direction = self::DIRECTION_UNKNOWN;
        $this->ref_roles = [self::ROLE_MINTER, self::ROLE_ISSUER];
        $this->ref_nft = $this->extractAffectedURITokenID();
        
      } else {
        //minter is new owner
        $this->ref_direction = self::DIRECTION_IN;
        $this->ref_roles = [self::ROLE_MINTER, self::ROLE_ISSUER, self::ROLE_OWNER];
        $this->ref_nft = $this->extractAffectedURITokenID();
      }
    } else {
      if(isset($this->tx->Destination)) {
        if($this->account == $this->tx->Destination) {
          $this->ref_direction = self::DIRECTION_IN;
          $this->ref_roles = [self::ROLE_OWNER];
          $this->ref_nft = $this->extractAffectedURITokenID();
        }
      }
      
    }
  }

  /**
   * Burns single token, it can burn own tokens or if is issuer and burnable
   * flag was set when minting, then issuer can burn owned token by someone else.
   * @return void
   */
  private function handleURITokenBurn(): void
  {
    if($this->account == $this->tx->Account) {
      $this->ref_roles = [self::ROLE_BURNER];
    }

    //extract issuer and owner of token from metadata
    foreach($this->tx->meta->AffectedNodes as $an) {
      if(isset($an->DeletedNode) && $an->DeletedNode->LedgerEntryType == 'URIToken') {
        
        if($an->DeletedNode->FinalFields->Issuer == $this->account) {
          $this->ref_roles[] = self::ROLE_ISSUER;
        }
        if($an->DeletedNode->FinalFields->Owner == $this->account) {
          $this->ref_roles[] = self::ROLE_OWNER;
          $this->ref_direction = self::DIRECTION_OUT;
          $this->ref_nft = $this->tx->URITokenID;
        }
      }
    }
  }

  /**
   * Creates new URIToken sell offer, no ownership change can occur.
   * @return void
   */
  private function handleURITokenCreateSellOffer(): void
  {
    if($this->account == $this->tx->Account) {
      $this->ref_direction = self::DIRECTION_UNKNOWN;
      $this->ref_roles = [self::ROLE_SELLER,self::ROLE_OWNER];
    }

    //extract issuer of token from metadata
    foreach($this->tx->meta->AffectedNodes as $an) {
      if(isset($an->ModifiedNode) && $an->ModifiedNode->LedgerEntryType == 'URIToken') {
        
        $issuer = $an->ModifiedNode->FinalFields->Issuer;
        if($issuer == $this->account) {
          $this->ref_roles[] = self::ROLE_ISSUER;
        }
      }
    }
  }

  private function handleURITokenCancelSellOffer(): void
  {
    if($this->account == $this->tx->Account) {
      $this->ref_direction = self::DIRECTION_UNKNOWN;
      $this->ref_roles = [self::ROLE_OWNER];
    }

    //extract issuer of token from metadata
    foreach($this->tx->meta->AffectedNodes as $an) {
      if(isset($an->ModifiedNode) && $an->ModifiedNode->LedgerEntryType == 'URIToken') {
        
        $issuer = $an->ModifiedNode->FinalFields->Issuer;
        if($issuer == $this->account) {
          $this->ref_roles[] = self::ROLE_ISSUER;
        }
      }
    }
  }

  private function handleRemit(): void
  {
    
    //if(!isset($this->tx->MintURIToken))
    //  return;

    if($this->account == $this->tx->Account) {
      // Remit creator perspective
      $nft = $this->extractAffectedURITokenID(false);
      $ref_nfts = $this->extractRemitURITokenIDs();
      if($nft == null && !count($ref_nfts)) return; //no affected nfts found

      $this->ref_direction = self::DIRECTION_OUT;
      if($nft != null) {
        //Minted nft found
        $this->ref_roles = [self::ROLE_ISSUER, self::ROLE_MINTER];
        $this->ref_nft = $nft;
      }

      if(count($ref_nfts)) {
        //NFT Transfers found
        $this->ref_roles[] = self::ROLE_SELLER;
        $this->ref_nfts = $ref_nfts;
      }
      
    } else if ($this->account == $this->tx->Destination) {
      //Remit receiver perspective
      $nft = $this->extractAffectedURITokenID(false);
      $ref_nfts = $this->extractRemitURITokenIDs();
      if($nft == null && !count($ref_nfts)) return; //no affected nfts found

      $this->ref_direction = self::DIRECTION_IN;
      if($nft != null) {
        //Minted nft found
        $this->ref_roles = [self::ROLE_OWNER];
        $this->ref_nft = $nft;
      }

      if(count($ref_nfts)) {
        //NFT Transfers found
        $this->ref_roles[] = self::ROLE_OWNER;
        $this->ref_nfts = $ref_nfts;
      }
    }
    $this->ref_roles = \array_unique($this->ref_roles);
  }


  private function extractRemitURITokenIDs(): array
  {
    if(!isset($this->tx->URITokenIDs))
      return [];
    return $this->tx->URITokenIDs;
  }

  /**
   * Extracts single URITokenID from metadata
   * Handled CreatedNode of type URIToken
   * @throws \Exception
   * @return string
   */
  private function extractAffectedURITokenID(bool $throwOnEmpty = true): ?string
  {
    if(isset($this->tx->URITokenID))
      return $this->tx->URITokenID;

    foreach($this->tx->meta->AffectedNodes as $an) {
      if(isset($an->CreatedNode) && $an->CreatedNode->LedgerEntryType == 'URIToken') {
        return $an->CreatedNode->LedgerIndex;
      }
    }
    if($throwOnEmpty)
      throw new \Exception('Unhandled: no URITokenID found in meta in tx ['.$this->tx->hash.']');
    return null;
  }

  /**
   * Extracts single NFTokenID from changes is NFTokenPages.
   * @throws \Exception
   * @return string
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

          
          //NFTokens in both PreviousFields and FinalFields are stated if change occured (ModifiedNode only)
          if(isset($affected_node->ModifiedNode->PreviousFields->NFTokens) && isset($affected_node->ModifiedNode->FinalFields->NFTokens)) {
            $inout = $this->extractNFTokenIDsFromNFTTokenPageChange(
              $affected_node->ModifiedNode->PreviousFields,
              $affected_node->ModifiedNode->FinalFields
            );
            $in = \array_merge($in,$inout['in']);
            $out = \array_merge($out,$inout['out']);
            unset($inout);
          }
          
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
    
    $diff = \array_diff($in,$out);
    if(count($diff) == 0)
      $diff = \array_diff($out,$in); //reverse direction
    
    $diff = \array_values($diff);

    if(count($diff) == 1)
      return $diff[0];

    if(count($diff) > 1)
      throw new \Exception('Unhandled multiple token changes in NFTTokenPage meta detected in tx ['.$this->tx->hash.']');
    
    if(count($diff) < 1)
      throw new \Exception('Unhandled no token changes in NFTTokenPage meta detected in tx ['.$this->tx->hash.']');

  }

  /**
   * Helper function that extracts token changes from single meta prev and final fields.
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

  /**
   * Checks first deleted node of type NFTokenOffer and extracts Owner and NFTokenID
   * @return array
   */
  private function extractDataFromDeletedOfferInMeta(): array
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

  /**
   * Extracts first found deleted node of type NFTokenOffer where Owner is $this->account and extracts Owner, NFTokenID and LedgerIndex
   * @return array
   */
  private function extractDataFromDeletedOfferInMeta_PriorityReferenceAccount(): array
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
    $roles = count($this->ref_roles) ? $this->ref_roles : [self::ROLE_UNKNOWN];
    \sort($roles,SORT_REGULAR);
    return [
      'nft' => $this->nft,
      'nfts' => $this->nfts,
      'context' => $this->context,
      'ref' => [
        'account' => $this->account,
        'nft' => $this->ref_nft,
        'nfts' => $this->ref_nfts,
        'direction' => $this->ref_direction,
        'roles'      => $roles
      ]
    ];
  }
}
