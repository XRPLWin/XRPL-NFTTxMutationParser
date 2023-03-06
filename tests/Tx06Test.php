<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * This transaction burns single token 000800002138571C1D5416A14CD66740650DF2C319918B360000099A00000000 issued by another account.
 * This is last in NFTokenPage and NFTokenPage is deleted.
 * @see https://hash.xrp.fans/4A9C86836EF487C2F25F258D91D7CA42B95C81E7C9F5CBA49C75697968FF0655/json
 */
final class Tx06Test extends TestCase
{
    public function testNFTokenBurnSelfLastByToken()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx06.json');
        $transaction = \json_decode($transaction);
        $account = "rV3WAvwwXgvPrYiUgSoytn9w3mejtPgLo";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();

        $this->assertIsArray($parsedTransaction);
        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals('000800002138571C1D5416A14CD66740650DF2C319918B360000099A00000000',$parsedTransaction['nftokenid']);
        $this->assertEquals('OUT',$parsedTransaction['direction']);
        $this->assertEquals('BURNER',$parsedTransaction['role']);
        
    }

    /**
     * From token issuer's perspective NFT burn has no effect since issuer is not owner of that NFT.
     */
    public function testNFTokenBurnByIssuer()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx06.json');
        $transaction = \json_decode($transaction);
        $account = "rhpe8vRiZ8NvVn6MnFTwL2TxzMeCUhSeVQ";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);
        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals(null,$parsedTransaction['nftokenid']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['direction']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['role']);
    }

    public function testNFTokenBurnByOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx06.json');
        $transaction = \json_decode($transaction);
        $account = "rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);
        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals(null,$parsedTransaction['nftokenid']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['direction']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['role']);
    }
}