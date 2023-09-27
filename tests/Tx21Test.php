<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * URITokenMint
 */
final class Tx21Test extends TestCase
{
  public function testUriTokenMinter()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx21.json');
      $transaction = \json_decode($transaction);
      $account = "rEiP3muQXyNVuASSEfGo9tGjnhoPHK8oww"; //this account bought token
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('CA781DB43A1AFEF26DFB38C6ECF1F76CAE00EE70979C6F8270AC8FA9AC3B05B4',$parsedTransaction['nft']);
      $this->assertEquals('CA781DB43A1AFEF26DFB38C6ECF1F76CAE00EE70979C6F8270AC8FA9AC3B05B4',$parsedTransaction['ref']['nft']);
      $this->assertEquals('IN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['MINTER'],$parsedTransaction['ref']['roles']);
  }

  public function testUriTokenOther()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx21.json');
      $transaction = \json_decode($transaction);
      $account = "r9gYbjBfANRfA1JHfaCVfPPGfXYiqQvmhS"; //this accont sold token
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('CA781DB43A1AFEF26DFB38C6ECF1F76CAE00EE70979C6F8270AC8FA9AC3B05B4',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
  }
}