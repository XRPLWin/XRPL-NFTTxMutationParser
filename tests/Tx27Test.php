<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * URITokenMint to different destination (owner)
 * Minting on behalf of new owner
 */
final class Tx27Test extends TestCase
{
  public function testUriTokenMinter()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx27.json');
      $transaction = \json_decode($transaction);
      $account = "rHyB8fpHCTB4NhwayEtNH9DsjLue33n1ph"; //minter
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);
      
      $this->assertEquals('39C0D52EDEF285103DF8A9CCE6F4E1A4AE206A76D46BA2AF4834135856E36840',$parsedTransaction['nft']);
      $this->assertEquals('39C0D52EDEF285103DF8A9CCE6F4E1A4AE206A76D46BA2AF4834135856E36840',$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['ISSUER','MINTER'],$parsedTransaction['ref']['roles']);
  }

  public function testUriTokenDestination()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx27.json');
      $transaction = \json_decode($transaction);
      $account = "rnBA8kVE8ZxqiRnUccKEo2x1Qa6sJWDXA9";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();
    //dd($parsedTransaction);
      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('39C0D52EDEF285103DF8A9CCE6F4E1A4AE206A76D46BA2AF4834135856E36840',$parsedTransaction['nft']);
      $this->assertEquals('39C0D52EDEF285103DF8A9CCE6F4E1A4AE206A76D46BA2AF4834135856E36840',$parsedTransaction['ref']['nft']);
      $this->assertEquals('IN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['OWNER'],$parsedTransaction['ref']['roles']);
  }

  public function testUriTokenOther()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx27.json');
      $transaction = \json_decode($transaction);
      $account = "r9gYbjBfANRfA1JHfaCVfPPGfXYiqQvmhS";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('39C0D52EDEF285103DF8A9CCE6F4E1A4AE206A76D46BA2AF4834135856E36840',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
  }
}