<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenCreateOffer issuer
 * @deprecated
 * @see B243CB8370ADD8770B7F055AA2B3792F87299A5F2C2A0D0B4865615B3A8CA727
 */
final class Tx19TestDev extends TestCase
{
  public function testTokenOwnerInCreateOffer()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx19.json');
      $transaction = \json_decode($transaction);
      $account = "rHaDANFTy4HQRqyEcL8qNKCdQ5xik8mhq4";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('000909C4B07EE3DB1FEF3DE3B0A49B205DA6E955D296CCE420AD122B00001CBE',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['ISSUER'],$parsedTransaction['ref']['roles']);
  }
}