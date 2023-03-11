<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * NFTokenMint
 * This test demonstrates correctly extracting token when NFTPages are dynamically extended or merged.
 * @see 8BEF5FDD7B1AA06078A4C86AE45E84C7657BE29AA9D4F9EB4234000783B7E94E
 */
final class Tx14Test extends TestCase
{

    public function testNFTokenMintByMinterWhenPagesShuffle()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx14.json');
        $transaction = \json_decode($transaction);
        $account = "rDPMFNFTsZtZxH86J3TozTXQUYiHGX52RS";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();

        $this->assertIsArray($parsedTransaction);

        $this->assertEquals(null,$parsedTransaction['context']);
        $this->assertEquals('000B000587DC091CBA7C019BF53206A4605ACA26F340F4D4FE25BCDE00000043',$parsedTransaction['nft']);
        $this->assertEquals('000B000587DC091CBA7C019BF53206A4605ACA26F340F4D4FE25BCDE00000043',$parsedTransaction['ref']['nft']);
        $this->assertEquals('IN',$parsedTransaction['ref']['direction']);
        $this->assertEquals(['MINTER','OWNER'],$parsedTransaction['ref']['roles']);

    }

    public function testNFTokenMintByOtherWhenPagesShuffle()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx14.json');
        $transaction = \json_decode($transaction);
        $account = "rvYAfWj5gh67oV6fW32ZzP3Aw4Eubs59B";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();

        $this->assertIsArray($parsedTransaction);

        $this->assertEquals(null,$parsedTransaction['context']);
        $this->assertEquals('000B000587DC091CBA7C019BF53206A4605ACA26F340F4D4FE25BCDE00000043',$parsedTransaction['nft']);
        $this->assertEquals(null,$parsedTransaction['ref']['nft']);
        $this->assertEquals('UNKNOWN',$parsedTransaction['ref']['direction']);
        $this->assertEquals(['UNKNOWN'],$parsedTransaction['ref']['roles']);
    }
}