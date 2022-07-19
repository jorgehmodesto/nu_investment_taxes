<?php

namespace App\Tests;

use App\Helpers\Taxes;
use PHPUnit\Framework\TestCase;

/**
 * Class TaxesTest
 * @package App\Tests
 */
class TaxesTest extends TestCase
{
    ##################################################################################
    ###################################### TESTS #####################################
    ##################################################################################
    /**
     * @param float $currentAvgPrice
     * @param int $position
     * @param array $transaction
     * @param float $expected
     *
     * @dataProvider calcAvgPriceProvider
     */
    public function testCalcAvgPrice(float $currentAvgPrice, int $position, array $transaction, float $expected): void
    {
        $taxes = new Taxes();

        $taxes->setAvgPrice($currentAvgPrice);
        $taxes->calcAvgPrice($position, $transaction);

        $this->assertEquals($expected, $taxes->getAvgPrice());
    }

    /**
     * @param array $transaction
     * @param float $avgPrice
     * @param float $expected
     *
     * @dataProvider getTransactionResultProvider
     */
    public function testGetTransactionResult(array $transaction, float $avgPrice, float $expected) : void
    {
        $taxes = new Taxes();

        $taxes->setAvgPrice($avgPrice);

        $this->assertEquals($expected, $taxes->getTransactionResult($transaction));
    }

    /**
     * @param string $orders
     * @param string $expected
     *
     * @dataProvider calculateProvider
     */
    public function testCalculate(string $orders, string $expected): void
    {
        $taxes = new Taxes();

        $taxes->setOrders($orders);

        $this->assertEquals($expected, $taxes->calculate()->toJson());
    }

    ##################################################################################
    ################################# DATA PROVIDERS #################################
    ##################################################################################
    /**
     * @see testCalcAvgPrice
     * @return array
     */
    public function calcAvgPriceProvider(): array
    {
        return [
            [
                10,
                1000,
                [
                    'quantity' => 1000,
                    'unit-cost' => 20
                ],
                15
            ],
            [
                10,
                1000,
                [
                    'quantity' => 1000,
                    'unit-cost' => 5
                ],
                7.5
            ]
        ];
    }

    /**
     * @see testGetTransactionResult
     * @return array
     */
    public function getTransactionResultProvider() : array
    {
        return [
            [
                [
                    'quantity' => 1000,
                    'unit-cost' => 10
                ],
                10,
                0
            ],
            [
                [
                    'quantity' => 1000,
                    'unit-cost' => 15
                ],
                10,
                5000
            ]
        ];
    }

    /**
     * @see testCalculate
     * @return array
     */
    public function calculateProvider(): array
    {
        return [
            [
                '[{"operation":"buy", "unit-cost":10.00, "quantity": 10000},{"operation":"sell", "unit-cost":20.00, "quantity": 5000}]',
                '[{"tax":0},{"tax":10000}]',
            ],
            [
                '[{"operation":"buy", "unit-cost":20.00, "quantity": 10000}, {"operation":"sell", "unit-cost":10.00, "quantity": 5000}]',
                '[{"tax":0},{"tax":0}]',
            ],
            [
                '[{"operation":"buy", "unit-cost":10.00, "quantity": 100},
                    {"operation":"sell", "unit-cost":15.00, "quantity": 50},
                    {"operation":"sell", "unit-cost":15.00, "quantity": 50}]',
                '[{"tax":0},{"tax":0},{"tax":0}]',
            ],
            [
                '[{"operation":"buy", "unit-cost":10.00, "quantity": 100},
                    {"operation":"sell", "unit-cost":15.00, "quantity": 50},
                    {"operation":"sell", "unit-cost":15.00, "quantity": 50}]',
                '[{"tax":0},{"tax":0},{"tax":0}]',
            ],
            [
                '[{"operation":"buy", "unit-cost":10.00, "quantity": 10000},
                    {"operation":"sell", "unit-cost":20.00, "quantity": 5000},
                    {"operation":"sell", "unit-cost":5.00, "quantity": 5000}]',
                '[{"tax":0},{"tax":10000},{"tax":0}]',
            ],
            [
                '[{"operation":"buy", "unit-cost":10.00, "quantity": 10000},
                    {"operation":"sell", "unit-cost":5.00, "quantity": 5000},
                    {"operation":"sell", "unit-cost":20.00, "quantity": 3000}]',
                '[{"tax":0},{"tax":0},{"tax":1000}]',
            ],
            [
                '[{"operation":"buy", "unit-cost":10.00, "quantity": 10000},
                    {"operation":"buy", "unit-cost":25.00, "quantity": 5000},
                    {"operation":"sell", "unit-cost":15.00, "quantity": 10000}]',
                '[{"tax":0},{"tax":0},{"tax":0}]',
            ],
            [
                '[{"operation":"buy", "unit-cost":10.00, "quantity": 10000},
                    {"operation":"buy", "unit-cost":25.00, "quantity": 5000},
                    {"operation":"sell", "unit-cost":15.00, "quantity": 10000},
                    {"operation":"sell", "unit-cost":25.00, "quantity": 5000}]',
                '[{"tax":0},{"tax":0},{"tax":0},{"tax":10000}]',
            ],
            [
                '[{"operation":"buy", "unit-cost":10.00, "quantity": 10000},
                    {"operation":"sell", "unit-cost":2.00, "quantity": 5000},
                    {"operation":"sell", "unit-cost":20.00, "quantity": 2000},
                    {"operation":"sell", "unit-cost":20.00, "quantity": 2000},
                    {"operation":"sell", "unit-cost":25.00, "quantity": 1000}]',
                '[{"tax":0},{"tax":0},{"tax":0},{"tax":0},{"tax":3000}]',
            ],
            [
                '[{"operation":"buy", "unit-cost":10.00, "quantity": 10000},
                    {"operation":"sell", "unit-cost":2.00, "quantity": 5000},
                    {"operation":"sell", "unit-cost":20.00, "quantity": 2000},
                    {"operation":"sell", "unit-cost":20.00, "quantity": 2000},
                    {"operation":"sell", "unit-cost":25.00, "quantity": 1000},
                    {"operation":"buy", "unit-cost":20.00, "quantity": 10000},
                    {"operation":"sell", "unit-cost":15.00, "quantity": 5000},
                    {"operation":"sell", "unit-cost":30.00, "quantity": 4350},
                    {"operation":"sell", "unit-cost":30.00, "quantity": 650}]',
                '[{"tax":0},{"tax":0},{"tax":0},{"tax":0},{"tax":3000},{"tax":0},{"tax":0},{"tax":3700},{"tax":0}]',
            ],
        ];
    }
}
