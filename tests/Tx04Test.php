<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * This is transaction when first token is minted on sender account and NFTokenPage is just created.
 * Token is minted to self.
 * @see https://hash.xrp.fans/937B47A71E1D7BF79A146DCE921070999F9752EADF2F9EBC48FDCCC5A0C2799D/json
 */
final class Tx04Test extends TestCase
{
    public function testNFTokenMintListBySender()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx04.json');
        $transaction = \json_decode($transaction);
        $account = "rKC3EJn1qMqkH1TwDLQ1pLaw4cLDsm9Rz7";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();

        $this->assertIsArray($parsedTransaction);
        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals('0008C350CCA81EA449D7466E0D3BA2298D5DE0C8FD4796E700334AC200000000',$parsedTransaction['nftokenid']);
        $this->assertEquals('IN',$parsedTransaction['direction']);
        $this->assertEquals(['MINTER','OWNER'],$parsedTransaction['roles']);
    }

    public function testNFTokenMintListByOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx04.json');
        $transaction = \json_decode($transaction);
        $account = "rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        
        $this->assertIsArray($parsedTransaction);
        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals(null,$parsedTransaction['nftokenid']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['direction']);
        $this->assertEquals(['UNKNOWN'],$parsedTransaction['roles']);
    }
}