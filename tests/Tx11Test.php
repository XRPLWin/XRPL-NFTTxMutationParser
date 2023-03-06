<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenAcceptOffer - Sell Offer
 * @see https://hash.xrp.fans/04EBA3FC9A54613CD782F8F659297E8FDAD8A2D1F7C6D7BE252419079D50483B/json
 */
final class Tx11Test extends TestCase
{
    /**
     * 
     */
    public function testNFTokenAcceptSellOfferByAccepter() //accepter/buyer
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx11.json');
        $transaction = \json_decode($transaction);
        $account = "rJezoKZH1nKavjZfjQAAZwvNHR6jabDfek";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        //dd($parsedTransaction);

        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals('00082710704B411C4B1627649C1224A381B3AD9C2D8F5B7A53C2AF3000000095',$parsedTransaction['nftokenid']);
        $this->assertEquals('IN',$parsedTransaction['direction']);

    }

    /**
     * 
     */
    public function testNFTokenAcceptOfferBySeller()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx11.json');
        $transaction = \json_decode($transaction);
        $account = "rBNkqEPXXzPtJs25yi1Q8SkLrBChdwsjrN";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        //dd($parsedTransaction);

        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals('00082710704B411C4B1627649C1224A381B3AD9C2D8F5B7A53C2AF3000000095',$parsedTransaction['nftokenid']);
        $this->assertEquals('OUT',$parsedTransaction['direction']);
    }

    public function testNFTokenAcceptOfferByOther()
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
}