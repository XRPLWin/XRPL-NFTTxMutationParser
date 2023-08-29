<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenAcceptOffer which is brokered (gains % xrp), also token issuer/creator gains % xrp
 * 
 * @see 4232E37C420528C097475AEADA784A33A42B35392A1A17E825E275430CD28A1A
 */
final class Tx16Test extends TestCase
{
  public function testBrokeredSellerPerspective()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx16.json');
      $transaction = \json_decode($transaction);
      $account = "rGR8Rh8PbrkjcJEHXHrTXPw9XXgdmWJbVK";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('00081388117867267123B7B71D690252208E2E3DA0F5BD69E9D72ECA00000D24',$parsedTransaction['nft']);
      $this->assertEquals('00081388117867267123B7B71D690252208E2E3DA0F5BD69E9D72ECA00000D24',$parsedTransaction['ref']['nft']);
      $this->assertEquals('OUT',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['SELLER'],$parsedTransaction['ref']['roles']);
  }

  public function testBrokeredBuyerPerspective()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx16.json');
      $transaction = \json_decode($transaction);
      $account = "rLc2ZXCkjt8amhDwharKVNohB9H6WEgf1H";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('00081388117867267123B7B71D690252208E2E3DA0F5BD69E9D72ECA00000D24',$parsedTransaction['nft']);
      $this->assertEquals('00081388117867267123B7B71D690252208E2E3DA0F5BD69E9D72ECA00000D24',$parsedTransaction['ref']['nft']);
      $this->assertEquals('IN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['BUYER','OWNER'],$parsedTransaction['ref']['roles']);
  }

  public function testBrokeredBrokerPerspective()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx16.json');
      $transaction = \json_decode($transaction);
      $account = "rpZqTPC8GvrSvEfFsUuHkmPCg29GdQuXhC";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('00081388117867267123B7B71D690252208E2E3DA0F5BD69E9D72ECA00000D24',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['BROKER'],$parsedTransaction['ref']['roles']);
  }

  public function testBrokeredIssuerPerspective()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx16.json');
      $transaction = \json_decode($transaction);
      $account = "rpb4jrU4r5jzdPFHY71s4zq924QGFt2tBg";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('00081388117867267123B7B71D690252208E2E3DA0F5BD69E9D72ECA00000D24',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['ISSUER'],$parsedTransaction['ref']['roles']);
  }
}