<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenPage is modified, token is minted to self.
 * @see https://hash.xrp.fans/B42C7A0C9C3061463C619999942D0F25E4AE5FB051EA0D7A4EE1A924DB6DFEE8/json
 */
final class Tx02Test extends TestCase
{
    public function testNFTokenMintListBySender()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx02.json');
        $transaction = \json_decode($transaction);
        $account = "rU2T6qNSab9N4SQZAEutwWnkzA7vUGWcfQ";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);
        $this->assertEquals(2,count($parsedTransaction));
        $this->assertArrayHasKey('in',$parsedTransaction);
        $this->assertArrayHasKey('out',$parsedTransaction);
        $this->assertEquals(['000000007EFD66D7DA6C495613C3ABE122007097122045BBC7169B2B00000190'],$parsedTransaction['in']);
        $this->assertEquals([],$parsedTransaction['out']);
    }

    public function testNFTokenMintListByOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx02.json');
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