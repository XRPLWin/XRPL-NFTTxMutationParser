<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * This is transaction when first token is minted on issuer account and NFTokenPage is just created.
 * Token is minted on behalf of issuer.
 * @see https://hash.xrp.fans/97F547EEDD12D5FC8F555B359FB7098A26D09C9E4E8B7FD9CEC1560ABEBF4341/json
 */
final class Tx03Test extends TestCase
{
    public function testNFTokenMintListByIssuer()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx03.json');
        $transaction = \json_decode($transaction);
        $account = "rKgR5LMCU1opzENpP7Qz7bRsQB4MKPpJb4";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();

        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals('00081770CCE71D9E7BD07E3A771C7619DA982D62CD37325A0000099A00000000',$parsedTransaction['nftokenid']);
        $this->assertEquals('IN',$parsedTransaction['direction']);
        
    }

    public function testNFTokenMintListBySender()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx03.json');
        $transaction = \json_decode($transaction);
        $account = "rfx2mVhTZzc6bLXKeYyFKtpha2LHrkNZFT";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();

        $this->assertIsArray($parsedTransaction);
        $this->assertArrayHasKey('nftokenid',$parsedTransaction);
        $this->assertArrayHasKey('direction',$parsedTransaction);
        $this->assertEquals(null,$parsedTransaction['nftokenid']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['direction']);
    }

    public function testNFTokenMintListByOther()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx03.json');
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