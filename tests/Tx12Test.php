<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenAcceptOffer - Brokered trade
 * @see 
 */
final class Tx12Test extends TestCase
{
    public function testNFTokenTradeByBroker()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx12.json');
        $transaction = \json_decode($transaction);
        $account = "rPpDcLBcRFYhUqeU9Rmmr5hgJWSkrL4VxP";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        //dd($parsedTransaction);

        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals(null,$parsedTransaction['nftokenid']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['direction']);

    }

    public function testNFTokenTradeBySeller()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx12.json');
        $transaction = \json_decode($transaction);
        $account = "rHjTJ9eWkPutj3X89sseaRe3kqeeLKMmbg";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        //dd($parsedTransaction);

        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals('000800FFB7896EF726023B37B8FC50B6D3623A464B2F883B0000099B00000000',$parsedTransaction['nftokenid']);
        $this->assertEquals('OUT',$parsedTransaction['direction']);

    }

    public function testNFTokenTradeByBuyer()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx12.json');
        $transaction = \json_decode($transaction);
        $account = "rDVcd1qz8Vhc84H8ZnA7B1XDomjagLyDFB";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        //dd($parsedTransaction);

        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals('000800FFB7896EF726023B37B8FC50B6D3623A464B2F883B0000099B00000000',$parsedTransaction['nftokenid']);
        $this->assertEquals('IN',$parsedTransaction['direction']);

    }

    public function testNFTokenTradeByOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx11.json');
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














    //OLD BELOW
    /**
     * This is buy offer by rsa614fckHaBjDpCcZNQqfvVFVPYZzPvE2, and rDuck4z5jdAJLDaRMwpc2xZhsCKqqTMRsr has accepted to sell.
     * In result rDuck4z5jdAJLDaRMwpc2xZhsCKqqTMRsr loses NFT.
     */
    /*public function testNFTokenAcceptBuyOfferByAccepter()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx12.json');
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
    }*/

    /**
     * This is buy offer by rsa614fckHaBjDpCcZNQqfvVFVPYZzPvE2, and rDuck4z5jdAJLDaRMwpc2xZhsCKqqTMRsr has accepted to sell.
     * In result rsa614fckHaBjDpCcZNQqfvVFVPYZzPvE2 gains NFT.
     */
    /*public function testNFTokenAcceptOfferByBuyer()
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
    }*/

    /*public function testNFTokenAcceptOfferByOther()
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
    }*/
}