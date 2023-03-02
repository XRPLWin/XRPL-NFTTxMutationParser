<?php declare(strict_types=1);

namespace XRPLWin\XRPLNFTTxMutatationParser\Tests;

use PHPUnit\Framework\TestCase;
use XRPLWin\XRPLNFTTxMutatationParser\NFTTxMutationParser;

/***
 * @see https://hash.xrp.fans/D904ADB2D6DD9644B7ACC14E351536B8570F8451AAB01E946ADB47B1E381399F/json
 */
final class Tx1Test extends TestCase
{
    public function testNFTokenMintList()
    {
        $transaction = file_get_contents(__DIR__.'/fixtures/tx1.json');
        $transaction = \json_decode($transaction);
        $account = "rHeRoYtbiMSKhtXm4k7tff1PrcwYnCePR3";
        $NFTTxMutationParser = new NFTTxMutationParser($account, $transaction->result);
        $parsedTransaction = $NFTTxMutationParser->result();
        dd($parsedTransaction);
        //Self (own account) must be $account
        $this->assertEquals($account,$parsedTransaction['self']['account']);

        # Basic info

        //Own account: two balance changes
        $this->assertEquals(2,count($parsedTransaction['self']['balanceChanges']));

        //Transaction type TRADE
        $this->assertEquals(TxMutationParser::MUTATIONTYPE_TRADE,$parsedTransaction['type']);

        $this->assertFalse($parsedTransaction['self']['feePayer']);

        # Event list

        //contains (correct) `primary` entry
        $this->assertArrayHasKey('primary',$parsedTransaction['eventList']);
        $this->assertEquals([
            'counterparty' => 'rCSCManTZ8ME9EoLrSHHYKW8PPwWMgkwr',
            'currency' => 'CSC',
            'value' => '1.999999999999'
        ],$parsedTransaction['eventList']['primary']);

        //contains (correct) `secondary` entry
        $this->assertArrayHasKey('secondary',$parsedTransaction['eventList']);
        $this->assertEquals([
            'currency' => 'XRP',
            'value' => '-0.004362'
        ],$parsedTransaction['eventList']['secondary']);

        # Event flow

        //contains (correct) `start` entry
        $this->assertArrayHasKey('start',$parsedTransaction['eventFlow']);
        $this->assertArrayHasKey('account',$parsedTransaction['eventFlow']['start']);
        $this->assertEquals('rogue5HnPRSszD9CWGSUz8UGHMVwSSKF6',$parsedTransaction['eventFlow']['start']['account']);

        //contains (correct) `intermediate` entry
        $this->assertArrayHasKey('intermediate',$parsedTransaction['eventFlow']);
        $this->assertEquals([
            'account' => $account,
            'mutations' => [
                'in' => [
                    'counterparty' => "rCSCManTZ8ME9EoLrSHHYKW8PPwWMgkwr",
                    'currency' => "CSC",
                    'value' => "1.999999999999",
                ],
                'out' => [
                    'currency' => "XRP",
                    'value' => "-0.004362",
                ]
            ]
        ],$parsedTransaction['eventFlow']['intermediate']);

        //contains (correct) `end` entry
        $this->assertArrayHasKey('end',$parsedTransaction['eventFlow']);
        $this->assertArrayHasKey('account',$parsedTransaction['eventFlow']['end']);
        $this->assertEquals('rogue5HnPRSszD9CWGSUz8UGHMVwSSKF6',$parsedTransaction['eventFlow']['end']['account']);

    }
}