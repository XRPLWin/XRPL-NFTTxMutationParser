[![CI workflow](https://github.com/XRPLWin/XRPL-NFTTxMutationParser/actions/workflows/main.yml/badge.svg)](https://github.com/XRPLWin/XRPL-NFTTxMutationParser/actions/workflows/main.yml)
[![GitHub license](https://img.shields.io/github/license/XRPLWin/XRPL-NFTTxMutationParser)](https://github.com/XRPLWin/XRPL-NFTTxMutationParser/blob/main/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/xrplwin/xrpl-nfttxmutationparser.svg?style=flat)](https://packagist.org/packages/xrplwin/xrpl-nfttxmutationparser)

# XRPL NFT Transaction Mutation Parser for PHP

## Demo

See this in action on [XRPLWin Playground](https://playground.xrpl.win/play/xrpl-nft-transaction-mutation-parser)

## Description

Parses NFToken and URIToken (referred as NFT) Transactions (`NFTokenMint`, `URITokenMint`, `NFTokenBurn`, `URITokenBurn`, `NFTokenAcceptOffer`, `URITokenBuy`, `NFTokenCancelOffer`, `URITokenCancelSellOffer`, `NFTokenCreateOffer`, `URITokenCreateSellOffer`) with account context and returns affected NFT, direction that NFT was transferred, minted or destroyed, and outputs roles referencing account has in specific transaction.

With this parser you can find out what has happened with referencing account after transaction was executed. For example when token is minted - parser will output token ID and direction IN, this means referenced account was minter and new token is added to reference account ownership.

What is checked:

- **Token id** - affected token ID in question
- **Token direction** - minted - IN, burned, OUT, sold - OUT, bought - IN
- **Roles** - role of referencing account in this transaction, is it minter, burner, seller, buyer, broker, or issuer

Note about issuer:  
Issuer can only happen in `NFTokenAcceptOffer` transaction type, it is extracted from modified AccountRoot node by checking if balance has been changed. If yes then this account gained percentage of sale, and it is issuer of NFToken.

### Note

This package is provided as is, please test it yourself first.  
Found a bug? [Report issue here](https://github.com/XRPLWin/XRPL-NFTTxMutationParser/issues/new)

## Requirements
- PHP 8.1 or higher
- [Composer](https://getcomposer.org/)

## Installation
To install run

```
composer require xrplwin/xrpl-nfttxmutationparser
```

## Usage
```PHP
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

$txResult = [
  "Account" => "rBcd..." 
  "Fee" => "1000",
  //...
];
$parser = new NFTTxMutationParser(
  "rAbc...", //This is reference account
  (object)$txResult //This is transaction result
);
$parsedTransaction = $parser->result();

print_r($parsedTransaction);

/*
┐
├ Output for $parsedTransaction:
├ Array (
├     [nft] => 00082710...
├     [context] => null  
├     [ref] => Array
├         (
├             [account] => rAbc...  
├             [nft] => 00082710...
├             [direction] => IN
├             [roles] => Array
├                 (
├                     [0] => OWNER
├                 )
├
├         )
├
├ )
┴
*/
```

## Response

| Key  | Type | Description |
| ------------- | ------------- | ------------- |
| nft  | ?String  | NFTokenID or URIToken always present in types: `NFTokenMint`, `NFTokenBurn`, `NFTokenAcceptOffer`, `NFTokenCreateOffer`, `URI*`   |
| context  | ?String  | Context of transaction (specifically offers). One of: `null`,`"BUY"`,`"SELL"`,`"BROKERED"` |
| ref.account  | String  | Reference account |
| ref.nft  | ?String  | NFTokenID or URIToken which changed ownership depending on direction for reference account |
| ref.direction  | String  | One of: `"IN"`,`"OUT"`,`"UNKNOWN"` |
| ref.roles  | Array  | Array of roles reference account has in this transaction, possible roles: `"UNKNOWN"`, `"OWNER"`, `"MINTER"`, `"BURNER"`, `"BUYER"`, `"SELLER"`, `"BROKER"`, `"ISSUER"`  |

## Running tests
Run all tests in "tests" directory.
```
composer test
```
or
```
./vendor/bin/phpunit --testdox
```
