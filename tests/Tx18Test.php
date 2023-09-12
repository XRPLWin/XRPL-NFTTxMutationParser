<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenCreateOffer check owner
 * 
 * @see 757C4C4E891819E0F31547506709F52B7B5E81CCB6F335198792D965B70ECE75
 */
final class Tx18Test extends TestCase
{
  public function testTokenOwnerInCreateOffer()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx18.json');
      $transaction = \json_decode($transaction);
      $account = "rGWv5YTG4ATZS6okStexXV5ZPRbGqb7E3k";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('0008138873749B2656849ADDE77BA4656E2E604AE3C7783050B84DDA00000440',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['OWNER'],$parsedTransaction['ref']['roles']);
  }
}