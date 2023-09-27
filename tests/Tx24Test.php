<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * URITokenBurn - burn by issuer
 */
final class Tx24Test extends TestCase
{
  public function testUriTokenBurnByIssuer()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx24.json');
      $transaction = \json_decode($transaction);
      $account = "raL76YeFLJxccNjkNkud5zkitaY8Kofqi6"; //this account is trying to sell token
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('69DAD8EB9740FC85F95DBCC4C087F489583F3CF206821EC20EB8FE9B85420612',$parsedTransaction['nft']);
      $this->assertEquals('69DAD8EB9740FC85F95DBCC4C087F489583F3CF206821EC20EB8FE9B85420612',$parsedTransaction['ref']['nft']);
      $this->assertEquals('OUT',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['BURNER','ISSUER','OWNER'],$parsedTransaction['ref']['roles']);
  }

  public function testUriTokenBurnByOwnerUnknown()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx24.json');
      $transaction = \json_decode($transaction);
      $account = "r3HfHxf6LeY8y1SGWHoKRRrUGdxruJyXEL"; //this account is trying to sell token
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('69DAD8EB9740FC85F95DBCC4C087F489583F3CF206821EC20EB8FE9B85420612',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
  }

  public function testUriTokenBurnByOther()
  {
      $transaction = file_get_contents(__DIR__.'/fixtures/tx24.json');
      $transaction = \json_decode($transaction);
      $account = "r9gYbjBfANRfA1JHfaCVfPPGfXYiqQvmhS";
      $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
      $parsedTransaction = $NFTTxMutationParser->result();

      $this->assertIsArray($parsedTransaction);

      $this->assertEquals('69DAD8EB9740FC85F95DBCC4C087F489583F3CF206821EC20EB8FE9B85420612',$parsedTransaction['nft']);
      $this->assertEquals(null,$parsedTransaction['ref']['nft']);
      $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
      $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
  }
}