<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenCreateOffer should have no changes for any participant.
 * @see https://hash.xrp.fans/780C44B2EDFF8FC4152B3F7E98D4C435C13DF9BB5498E4BB2D019FCC7EF45BC6/json
 */
final class Tx08Test extends TestCase
{
    public function testNFTokenCreateOfferByInitiator()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx08.json');
        $transaction = \json_decode($transaction);
        $account = "rPmjAYZJ6WgxoVcpnteZWYUSXfh8RaGnD2";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();

        $this->assertIsArray($parsedTransaction);

        $this->assertEquals('SELL',$parsedTransaction['context']);
        $this->assertEquals('00082710F9C8EA172039A645AE3E96C10D20A3EF938D92F9B52AF54A00003BB0',$parsedTransaction['nft']);
        $this->assertEquals(null,$parsedTransaction['ref']['nft']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
        $this->assertEquals(['OWNER'],$parsedTransaction['ref']['roles']);

    }
    
    public function testNFTokenCreateOfferByDestination()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx08.json');
        $transaction = \json_decode($transaction);
        $account = "raAMrBFaAqf7cv8U7gZssZptfaKGvZjiga";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();

        $this->assertIsArray($parsedTransaction);

        $this->assertEquals('SELL',$parsedTransaction['context']);
        $this->assertEquals('00082710F9C8EA172039A645AE3E96C10D20A3EF938D92F9B52AF54A00003BB0',$parsedTransaction['nft']);
        $this->assertEquals(null,$parsedTransaction['ref']['nft']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
        $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);

    }

    public function testNFTokenCreateOfferByOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx08.json');
        $transaction = \json_decode($transaction);
        $account = "rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);

        $this->assertEquals('SELL',$parsedTransaction['context']);
        $this->assertEquals('00082710F9C8EA172039A645AE3E96C10D20A3EF938D92F9B52AF54A00003BB0',$parsedTransaction['nft']);
        $this->assertEquals(null,$parsedTransaction['ref']['nft']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
        $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
    }
}