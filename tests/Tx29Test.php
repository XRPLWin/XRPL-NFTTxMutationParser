<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * Remit
 * Transfer two URITokens
 */
final class Tx29Test extends TestCase
{
  public function testRemitSenderMinter()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx29.json');
      $transaction = \json_decode($transaction);
      $account = "r3RtnU293vBgLCHvCRmo2goUECnnMVS5qA"; //minter
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);
      $this->assertEquals(null,$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('OUT',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['SELLER'],$parsedTransaction['ref']['roles']);

      $this->assertIsArray($parsedTransaction['nfts']);
      $this->assertEquals([
        '38B40CB673CB072DEBB7FBE798162570B081BA6E89652BF2FDA0C522A1612920',
        '02E51D07084FA44F7D7B521EE1F0439E7B4FB0ADD98F129C8FDDEA643A9ABCC7'
      ],$parsedTransaction['nfts']);
      $this->assertEquals([
        '38B40CB673CB072DEBB7FBE798162570B081BA6E89652BF2FDA0C522A1612920',
        '02E51D07084FA44F7D7B521EE1F0439E7B4FB0ADD98F129C8FDDEA643A9ABCC7'
      ],$parsedTransaction['ref']['nfts']);
  }

  public function testRemitDestination()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx29.json');
      $transaction = \json_decode($transaction);
      $account = "rBL1AMHX2J1uoMKZqpViHSBBcMbLpGpf9i";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();
      $this->assertIsArray($parsedTransaction);

      $this->assertIsArray($parsedTransaction);
      $this->assertEquals(null,$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('IN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['OWNER'],$parsedTransaction['ref']['roles']);

      $this->assertIsArray($parsedTransaction['nfts']);
      $this->assertEquals([
        '38B40CB673CB072DEBB7FBE798162570B081BA6E89652BF2FDA0C522A1612920',
        '02E51D07084FA44F7D7B521EE1F0439E7B4FB0ADD98F129C8FDDEA643A9ABCC7'
      ],$parsedTransaction['nfts']);
      $this->assertEquals([
        '38B40CB673CB072DEBB7FBE798162570B081BA6E89652BF2FDA0C522A1612920',
        '02E51D07084FA44F7D7B521EE1F0439E7B4FB0ADD98F129C8FDDEA643A9ABCC7'
      ],$parsedTransaction['ref']['nfts']);
  }

  public function testRemitOther()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx29.json');
      $transaction = \json_decode($transaction);
      $account = "r9gYbjBfANRfA1JHfaCVfPPGfXYiqQvmhS";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);
      $this->assertEquals(null,$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
      $this->assertIsArray($parsedTransaction['nfts']);
      $this->assertEquals([
        '38B40CB673CB072DEBB7FBE798162570B081BA6E89652BF2FDA0C522A1612920',
        '02E51D07084FA44F7D7B521EE1F0439E7B4FB0ADD98F129C8FDDEA643A9ABCC7'
      ],$parsedTransaction['nfts']);
      $this->assertEquals([],$parsedTransaction['ref']['nfts']);
  }

}