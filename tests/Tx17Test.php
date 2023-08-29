<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenAcceptOffer direct trade without any balances
 * 
 * @see D05651E045478BD1D7A08972ADBFC9EA8B3975A2220FCCDAA13CA6E5D5790A04
 */
final class Tx17Test extends TestCase
{
  public function testBrokeredSellerPerspective()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx17.json');
      $transaction = \json_decode($transaction);
      $account = "r3yrRsNt7i7DSKz9tTLwCRaJVQuPQvLfJc";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('00081B583865C422F1285CD474817CF1E067373D70CA9E2B734B2A5600000CDC',$parsedTransaction['nft']);
      $this->assertEquals('00081B583865C422F1285CD474817CF1E067373D70CA9E2B734B2A5600000CDC',$parsedTransaction['ref']['nft']);
      $this->assertEquals('OUT',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['SELLER'],$parsedTransaction['ref']['roles']);
  }

  public function testBrokeredBuyerPerspective()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx17.json');
      $transaction = \json_decode($transaction);
      $account = "rHxYgArnJGvhqa8FXdR57J4vrKKuzNjwx2";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('00081B583865C422F1285CD474817CF1E067373D70CA9E2B734B2A5600000CDC',$parsedTransaction['nft']);
      $this->assertEquals('00081B583865C422F1285CD474817CF1E067373D70CA9E2B734B2A5600000CDC',$parsedTransaction['ref']['nft']);
      $this->assertEquals('IN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['BUYER','OWNER'],$parsedTransaction['ref']['roles']);
  }

  public function testBrokeredOtherPerspective()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx17.json');
      $transaction = \json_decode($transaction);
      $account = "rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('00081B583865C422F1285CD474817CF1E067373D70CA9E2B734B2A5600000CDC',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
  }
}