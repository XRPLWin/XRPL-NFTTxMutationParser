<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * This transaction burns single token 000800002138571C1D5416A14CD66740650DF2C319918B3616E5DA9D00000001 (self)
 * @see https://hash.xrp.fans/7B9EFDFDC801C58F2B61B89AA2751634F49CE2A93923671FF0F4F099C7EE17FF/json
 */
final class Tx05Test extends TestCase
{
    public function testNFTokenBurnBySelf()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx05.json');
        $transaction = \json_decode($transaction);
        $account = "rhpe8vRiZ8NvVn6MnFTwL2TxzMeCUhSeVQ";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();

        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals('000800002138571C1D5416A14CD66740650DF2C319918B3616E5DA9D00000001',$parsedTransaction['nftokenid']);
        $this->assertEquals('OUT',$parsedTransaction['direction']);
      
    }

    public function testNFTokenBurnByOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx05.json');
        $transaction = \json_decode($transaction);
        $account = "rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);
        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals(null,$parsedTransaction['nftokenid']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['direction']);
    }
}