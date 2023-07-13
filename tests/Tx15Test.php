<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenBurn last NFTokenPage was deleted
 * 
 * @see 98C6761197D6669ED6861E09DAA033228FBD68CF9C9AB9FB68DF67FC05580EE8
 */
final class Tx15Test extends TestCase
{
  public function testNFTokenBurnLastPageDeleted()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx15.json');
      $transaction = \json_decode($transaction);
      $account = "rDB9Fd4SP1drhHc7BZqpGY5rrpDC6ZoMpu";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('000A27106D6349BB3F5965F24292DD17C93874EAAEA38DA800AA41C100000325',$parsedTransaction['nft']);
      $this->assertEquals('000A27106D6349BB3F5965F24292DD17C93874EAAEA38DA800AA41C100000325',$parsedTransaction['ref']['nft']);
      $this->assertEquals('OUT',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['BURNER','OWNER'],$parsedTransaction['ref']['roles']);
      
  }
}