<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * URITokenCancelSellOffer same issuer and owner
 */
final class Tx26Test extends TestCase
{
  public function testUriTokenCancelSellOfferByInitiator()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx26.json');
      $transaction = \json_decode($transaction);
      $account = "rnahZg31eWFdS4Vx4m9MGXX5JofdiyYb3Y";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();
      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('F2AD40BE498122FBEB9E4D07F460FB842895075DD2C5ED8ACFFE2E69C33D10E0',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['ISSUER','OWNER'],$parsedTransaction['ref']['roles']);
  }

  public function testUriTokenBurnByOther()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx26.json');
      $transaction = \json_decode($transaction);
      $account = "r9gYbjBfANRfA1JHfaCVfPPGfXYiqQvmhS";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('F2AD40BE498122FBEB9E4D07F460FB842895075DD2C5ED8ACFFE2E69C33D10E0',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
  }
}