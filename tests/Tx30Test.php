<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenModify
 * Modify URI
 */
final class Tx30Test extends TestCase
{
  public function testModifyUri()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx30.json');
      $transaction = \json_decode($transaction);
      $account = "rWinEUKtN3BmYdDoGU6HZ7tTG54BeCAiz"; //modifier
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();
      
      $this->assertIsArray($parsedTransaction);
      $this->assertEquals('0013000005A506F1996C9E045C1CE7EE7DF7E599C19A6D529E83D978041C62DD',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);

      $this->assertIsArray($parsedTransaction['nfts']);
      $this->assertEquals([],$parsedTransaction['nfts']);
      $this->assertIsArray($parsedTransaction['ref']['nfts']);
      $this->assertEquals([],$parsedTransaction['ref']['nfts']);
  }

}