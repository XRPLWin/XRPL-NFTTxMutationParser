<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * This transaction burns single token 00090D7570357F6122254CF8FE8B6984D11E38AE316F95C944B17C9E00000003 issued by another account.
 * This is second to last in NFTokenPage and NFTokenPage is not deleted.
 * @see C699F0AA5FC03A091A19C8CB5666A7BB9FE6A721FE00906E9D73EE03D2E3AF09 (testnet)
 */
final class Tx07Test extends TestCase
{
    public function testNFTokenBurnByIssuerOrAuthorized()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx07.json');
        $transaction = \json_decode($transaction);
        $account = "rBNJmZ25nR29pF5yYMu6PaJ9GJBPi2QfV5";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);
        $this->assertEquals(2,count($parsedTransaction));
        $this->assertArrayHasKey('in',$parsedTransaction);
        $this->assertArrayHasKey('out',$parsedTransaction);
        $this->assertEquals([],$parsedTransaction['in']);
        $this->assertEquals([],$parsedTransaction['out']);
    }

    /**
     * From token issuer's perspective NFT burn has no effect since issuer is not owner of that NFT.
     */
    public function testNFTokenBurnByIssuerFromOwnerPerspective()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx07.json');
        $transaction = \json_decode($transaction);
        $account = "r3c7yhxjCZzdk6kJCNYKB4nwZbJyKmVuZx";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);
        $this->assertEquals(2,count($parsedTransaction));
        $this->assertArrayHasKey('in',$parsedTransaction);
        $this->assertArrayHasKey('out',$parsedTransaction);
        $this->assertEquals([],$parsedTransaction['in']);
        $this->assertEquals(['00090D7570357F6122254CF8FE8B6984D11E38AE316F95C944B17C9E00000003'],$parsedTransaction['out']);
    }

    public function testNFTokenBurnByOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx07.json');
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