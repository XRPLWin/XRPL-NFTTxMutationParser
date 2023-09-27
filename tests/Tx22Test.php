<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * URITokenBurn
 */
final class Tx22Test extends TestCase
{
  public function testUriTokenBurner()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx22.json');
      $transaction = \json_decode($transaction);
      $account = "rEiP3muQXyNVuASSEfGo9tGjnhoPHK8oww";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('0D8C3949411B396ECE07E9F574482B52377BE8FB4E5D108E94C9B39888EF0CDB',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['BURNER','ISSUER'],$parsedTransaction['ref']['roles']);
  }

  public function testUriTokenOwner()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx22.json');
      $transaction = \json_decode($transaction);
      $account = "rJNTKV22U8n9uBkCsdc8W9ABaiVs1AVwR4";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('0D8C3949411B396ECE07E9F574482B52377BE8FB4E5D108E94C9B39888EF0CDB',$parsedTransaction['nft']);
      $this->assertEquals('0D8C3949411B396ECE07E9F574482B52377BE8FB4E5D108E94C9B39888EF0CDB',$parsedTransaction['ref']['nft']);
      $this->assertEquals('OUT',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['OWNER'],$parsedTransaction['ref']['roles']);
  }

  public function testUriTokenBurnByOtherPerspective()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx22.json');
      $transaction = \json_decode($transaction);
      $account = "r9gYbjBfANRfA1JHfaCVfPPGfXYiqQvmhS";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('0D8C3949411B396ECE07E9F574482B52377BE8FB4E5D108E94C9B39888EF0CDB',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
  }
}