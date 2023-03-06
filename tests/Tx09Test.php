<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenCancelOffer should have no changes for any participant.
 * @see https://hash.xrp.fans/9FF6366C19F762AE3479DC01390CDE17F1055EFF0C52A28B8ACF0CC11AEF0CC5/json
 */
final class Tx09Test extends TestCase
{
    public function testNFTokenCancelOfferByInitiator()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx09.json');
        $transaction = \json_decode($transaction);
        $account = "rnkmrTjpPTnVHkWCkVHLLVpspLKhyBCPm5";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);
        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals(null,$parsedTransaction['nftokenid']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['direction']);
    }

    public function testNFTokenCancelOfferByOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx09.json');
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