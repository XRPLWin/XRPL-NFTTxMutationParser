<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * URITokenBuy
 */
final class Tx20Test extends TestCase
{
  public function testUriTokenBuyer()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx20.json');
      $transaction = \json_decode($transaction);
      $account = "rHqSYyo218SHXydoNatcLnj3ec4CWJVCFZ"; //this account bought token
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('A975A3B6C1358FEBDCB678E0C3353AD49D6F6527E0B785E87392B15C713B3221',$parsedTransaction['nft']);
      $this->assertEquals('A975A3B6C1358FEBDCB678E0C3353AD49D6F6527E0B785E87392B15C713B3221',$parsedTransaction['ref']['nft']);
      $this->assertEquals('IN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['BUYER','OWNER'],$parsedTransaction['ref']['roles']);
  }

  public function testUriTokenSeller()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx20.json');
      $transaction = \json_decode($transaction);
      $account = "rEiP3muQXyNVuASSEfGo9tGjnhoPHK8oww"; //this accont sold token
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();
      
      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('A975A3B6C1358FEBDCB678E0C3353AD49D6F6527E0B785E87392B15C713B3221',$parsedTransaction['nft']);
      $this->assertEquals('A975A3B6C1358FEBDCB678E0C3353AD49D6F6527E0B785E87392B15C713B3221',$parsedTransaction['ref']['nft']);
      $this->assertEquals('OUT',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['SELLER'],$parsedTransaction['ref']['roles']);
  }

  public function testUriTokenOther()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx20.json');
      $transaction = \json_decode($transaction);
      $account = "r9gYbjBfANRfA1JHfaCVfPPGfXYiqQvmhS"; //this accont sold token
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('A975A3B6C1358FEBDCB678E0C3353AD49D6F6527E0B785E87392B15C713B3221',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
  }
}