<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenPage is modified, token is minted on behalf of issuer.
 * @see https://hash.xrp.fans/D904ADB2D6DD9644B7ACC14E351536B8570F8451AAB01E946ADB47B1E381399F/json
 */
final class Tx01Test extends TestCase
{
    public function testNFTokenMintListIssuer()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx01.json');
        $transaction = \json_decode($transaction);
        $account = "rHeRoYtbiMSKhtXm4k7tff1PrcwYnCePR3";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();

        $this->assertIsArray($parsedTransaction);
        $this->assertEquals(2,count($parsedTransaction));
        $this->assertArrayHasKey('in',$parsedTransaction);
        $this->assertArrayHasKey('out',$parsedTransaction);
        $this->assertEquals(['00082710B6961B76BA53FED0D85EF7267A4DBD6152FF1C06C11C4978000001DE'],$parsedTransaction['in']);
        $this->assertEquals([],$parsedTransaction['out']);
    }

    public function testNFTokenMintListSender()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx01.json');
        $transaction = \json_decode($transaction);
        $account = "rfx2mVhTZzc6bLXKeYyFKtpha2LHrkNZFT";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);
        $this->assertEquals(2,count($parsedTransaction));
        $this->assertArrayHasKey('in',$parsedTransaction);
        $this->assertArrayHasKey('out',$parsedTransaction);
        $this->assertEquals([],$parsedTransaction['in']);
        $this->assertEquals([],$parsedTransaction['out']);
    }

    public function testNFTokenMintListOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx01.json');
        $transaction = \json_decode($transaction);
        $account = "rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);
        $this->assertEquals(2,count($parsedTransaction));
        $this->assertArrayHasKey('in',$parsedTransaction);
        $this->assertArrayHasKey('out',$parsedTransaction);
        $this->assertEquals([],$parsedTransaction['in']);
        $this->assertEquals([],$parsedTransaction['out']);
    }
}