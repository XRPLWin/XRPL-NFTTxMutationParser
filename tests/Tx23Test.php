<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * URITokenCreateSellOffer
 */
final class Tx23Test extends TestCase
{
  public function testUriTokenCreateSellOfferByOfferCreator()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx23.json');
      $transaction = \json_decode($transaction);
      $account = "rHJqCseZFzCveSTdtJuDNpD4ARoMy41E1C"; //this account is trying to sell token
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('30745829F9817AB638094EA3A085DAA03BD4C9E5DF3C6C68C7DE79D8771E2A04',$parsedTransaction['nft']);
      $this->assertEquals('30745829F9817AB638094EA3A085DAA03BD4C9E5DF3C6C68C7DE79D8771E2A04',$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['ISSUER','OWNER','SELLER'],$parsedTransaction['ref']['roles']);
  }

  public function testUriTokenCreateSellOfferByOther()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx23.json');
      $transaction = \json_decode($transaction);
      $account = "r9gYbjBfANRfA1JHfaCVfPPGfXYiqQvmhS";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('30745829F9817AB638094EA3A085DAA03BD4C9E5DF3C6C68C7DE79D8771E2A04',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
  }
}