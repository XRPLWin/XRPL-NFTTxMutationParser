<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * Remit
 * Mint URIToken
 */
final class Tx28Test extends TestCase
{
  public function testRemitSenderMinter()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx28.json');
      $transaction = \json_decode($transaction);
      $account = "r3RtnU293vBgLCHvCRmo2goUECnnMVS5qA"; //minter
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);
      
      $this->assertEquals('80B3BD46EBBFDFD0317CCFE7F533988A73A4281FABF1289242D00F8ED1C61878',$parsedTransaction['nft']);
      $this->assertEquals('80B3BD46EBBFDFD0317CCFE7F533988A73A4281FABF1289242D00F8ED1C61878',$parsedTransaction['ref']['nft']);
      $this->assertEquals('OUT',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['ISSUER','MINTER'],$parsedTransaction['ref']['roles']);
  }

  public function testRemitDestination()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx28.json');
      $transaction = \json_decode($transaction);
      $account = "rBL1AMHX2J1uoMKZqpViHSBBcMbLpGpf9i";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();
      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('80B3BD46EBBFDFD0317CCFE7F533988A73A4281FABF1289242D00F8ED1C61878',$parsedTransaction['nft']);
      $this->assertEquals('80B3BD46EBBFDFD0317CCFE7F533988A73A4281FABF1289242D00F8ED1C61878',$parsedTransaction['ref']['nft']);
      $this->assertEquals('IN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['OWNER'],$parsedTransaction['ref']['roles']);
  }

  public function testRemitOther()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx28.json');
      $transaction = \json_decode($transaction);
      $account = "r9gYbjBfANRfA1JHfaCVfPPGfXYiqQvmhS";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);
      $this->assertEquals('80B3BD46EBBFDFD0317CCFE7F533988A73A4281FABF1289242D00F8ED1C61878',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
  }
}