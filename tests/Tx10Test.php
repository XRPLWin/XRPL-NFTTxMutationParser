<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenAcceptOffer - Buy Offer
 * @see https://hash.xrp.fans/9FF6366C19F762AE3479DC01390CDE17F1055EFF0C52A28B8ACF0CC11AEF0CC5/json
 * TODO: 04EBA3FC9A54613CD782F8F659297E8FDAD8A2D1F7C6D7BE252419079D50483B
 */
final class Tx10Test extends TestCase
{
    /**
     * This is buy offer by rsa614fckHaBjDpCcZNQqfvVFVPYZzPvE2, and rDuck4z5jdAJLDaRMwpc2xZhsCKqqTMRsr has accepted to sell.
     * In result rDuck4z5jdAJLDaRMwpc2xZhsCKqqTMRsr loses NFT.
     */
    public function testNFTokenAcceptBuyOfferByAccepter()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx10.json');
        $transaction = \json_decode($transaction);
        $account = "rDuck4z5jdAJLDaRMwpc2xZhsCKqqTMRsr";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        //dd($parsedTransaction);
        $this->assertIsArray($parsedTransaction);
        $this->assertEquals(2,count($parsedTransaction));
        $this->assertArrayHasKey('in',$parsedTransaction);
        $this->assertArrayHasKey('out',$parsedTransaction);
        $this->assertEquals([],$parsedTransaction['in']);
        $this->assertEquals(['00081702153AA708D64FF2E79DFE9D2D8E27845F9AB4E3C800DA2FC700003A2C'],$parsedTransaction['out']);
    }

    /**
     * This is buy offer by rsa614fckHaBjDpCcZNQqfvVFVPYZzPvE2, and rDuck4z5jdAJLDaRMwpc2xZhsCKqqTMRsr has accepted to sell.
     * In result rsa614fckHaBjDpCcZNQqfvVFVPYZzPvE2 gains NFT.
     */
    public function testNFTokenAcceptOfferByBuyer()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx10.json');
        $transaction = \json_decode($transaction);
        $account = "rsa614fckHaBjDpCcZNQqfvVFVPYZzPvE2";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        //dd($parsedTransaction);
        $this->assertIsArray($parsedTransaction);
        $this->assertEquals(2,count($parsedTransaction));
        $this->assertArrayHasKey('in',$parsedTransaction);
        $this->assertArrayHasKey('out',$parsedTransaction);
        $this->assertEquals(['00081702153AA708D64FF2E79DFE9D2D8E27845F9AB4E3C800DA2FC700003A2C'],$parsedTransaction['in']);
        $this->assertEquals([],$parsedTransaction['out']);
    }

    public function testNFTokenAcceptOfferByOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx10.json');
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