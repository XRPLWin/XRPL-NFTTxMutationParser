<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenAcceptOffer - Buy Offer
 * @see https://hash.xrp.fans/BEB64444C36D1072820BAED317BE2E6470AFDAD9D8FB2D16A15A4D46E5A71909/json
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

        $this->assertIsArray($parsedTransaction);

        $this->assertEquals('BUY',$parsedTransaction['context']);
        $this->assertEquals('00081702153AA708D64FF2E79DFE9D2D8E27845F9AB4E3C800DA2FC700003A2C',$parsedTransaction['nft']);
        $this->assertEquals('00081702153AA708D64FF2E79DFE9D2D8E27845F9AB4E3C800DA2FC700003A2C',$parsedTransaction['ref']['nft']);
        $this->assertEquals('OUT',$parsedTransaction['ref']['direction']);
        $this->assertEquals(['SELLER'],$parsedTransaction['ref']['roles']);
        
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

        $this->assertIsArray($parsedTransaction);

        $this->assertEquals('BUY',$parsedTransaction['context']);
        $this->assertEquals('00081702153AA708D64FF2E79DFE9D2D8E27845F9AB4E3C800DA2FC700003A2C',$parsedTransaction['nft']);
        $this->assertEquals('00081702153AA708D64FF2E79DFE9D2D8E27845F9AB4E3C800DA2FC700003A2C',$parsedTransaction['ref']['nft']);
        $this->assertEquals('IN',$parsedTransaction['ref']['direction']);
        $this->assertEquals(['BUYER','OWNER'],$parsedTransaction['ref']['roles']); //this account is new owner, state after finalizing transaction

    }

    public function testNFTokenAcceptOfferByIssuer()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx10.json');
        $transaction = \json_decode($transaction);
        $account = "rpAETGuhJW5ZYfg3PdsCTELv4ho147AoE9";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();

        $this->assertIsArray($parsedTransaction);

        $this->assertEquals('BUY',$parsedTransaction['context']);
        $this->assertEquals('00081702153AA708D64FF2E79DFE9D2D8E27845F9AB4E3C800DA2FC700003A2C',$parsedTransaction['nft']);
        $this->assertEquals(null,$parsedTransaction['ref']['nft']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
        $this->assertEquals(['ISSUER'],$parsedTransaction['ref']['roles']);

    }

    public function testNFTokenAcceptOfferByOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx10.json');
        $transaction = \json_decode($transaction);
        $account = "rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);

        $this->assertEquals('BUY',$parsedTransaction['context']);
        $this->assertEquals('00081702153AA708D64FF2E79DFE9D2D8E27845F9AB4E3C800DA2FC700003A2C',$parsedTransaction['nft']);
        $this->assertEquals(null,$parsedTransaction['ref']['nft']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
        $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
    }
}