<?php

namespace App\Helpers;

/**
 * Class Tax
 * @package App\Helpers
 */
class Taxes
{
    /**
     * @var array $taxes
     *   Calculated taxes
     */
    protected $taxes = [];

    /**
     * @var array $orders
     *   Orders to calculate taxes.
     */
    protected $orders = [];

    /**
     * @var array $errors
     *   Errors during the calculation.
     */
    protected $errors = [];

    /**
     * @var float $loss
     *   Property to store historical loss.
     */
    protected $loss = 0.0;

    /**
     * @var int $position
     *   Current position.
     */
    protected $position = 0;

    /**
     * @var float $avgPrice
     *   Average price per unit in current position.
     */
    protected $avgPrice = 0.0;

    const BUY_OPERATION = 'buy';

    const SELL_OPERATION = 'sell';

    const TAX_PERCENTAGE = 0.2;

    const MIN_TRANSATION_VALUE_TO_PAY_TAXES = 20000;

    /**
     * Tax constructor.
     * @param array $orders
     */
    public function __construct($orders = [])
    {
        $this->setOrders($orders);
    }

    public function calculate() : self
    {
        $taxes = array_map(function($transactions) {

            $transactions = json_decode($transactions, true);
            $transactionTaxes = [];

            foreach ($transactions as $transaction) {
                if (!in_array($transaction['operation'], [
                    self::BUY_OPERATION, self::SELL_OPERATION
                ])) {
                    array_push($transactionTaxes, ["tax" => 0]);
                    $this->setError(
                        "Invalid operation ({$transaction['operation']}) - Transaction: " . json_encode($transaction)
                    );
                    continue;
                }

                $position = $this->getPosition();

                if ($transaction['operation'] == self::BUY_OPERATION) {
                    array_push($transactionTaxes, ["tax" => 0]);

                    $avgPrice = $this->calcAvgPrice($position, $transaction);

                    $position += $transaction['quantity'];

                    $this->setPosition($position);
                    $this->setAvgPrice($avgPrice);

                    continue;
                }

                $tax = 0.0;
                $profit = $this->getOperationProfit($transaction);

                if ($profit < 0) {
                    $loss = $this->getLoss() + $profit;
                    $this->setLoss($loss);
                }

                if (
                    ($transaction['quantity'] * $transaction['unit-cost']) > self::MIN_TRANSATION_VALUE_TO_PAY_TAXES &&
                    $profit > 0
                ) {
                    $tax = round($profit * self::TAX_PERCENTAGE, 2);
                }

                $position -= $transaction['quantity'];
                $avgPrice = $this->calcAvgPrice($position, $transaction);

                $this->setPosition($position);
                $this->setAvgPrice($avgPrice);

                array_push($transactionTaxes, ["tax" => $tax]);
            }

            return $transactionTaxes;

        }, $this->getOrders());

        $this->setTaxes($taxes);

        return $this;
    }

    /**
     * Method to return taxes as JSON format.
     *
     * @return string
     */
    public function toJson() : string
    {
        if (is_array($this->taxes)) {
            return json_encode($this->taxes);
        }

        return '[]';
    }

    /**
     * @return int
     */
    public function getPosition() : int
    {
        return $this->position;
    }

    public function setPosition(int $position) : self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return array
     */
    public function getTaxes() : array
    {
        return $this->taxes;
    }

    /**
     * @param array $taxes
     * @return Taxes
     */
    public function setTaxes(array $taxes) : self
    {
        $this->taxes = $taxes;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrders() : array
    {
        return $this->orders;
    }

    /**
     * @param array $orders
     * @return Taxes
     */
    public function setOrders(array $orders) : self
    {
        $this->orders = $orders;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * @param string $error
     * @return Taxes
     */
    private function setError(string $error) : self
    {
        if (!empty($error)) {
            array_push($this->errors, $error);
        }

        return $this;
    }

    /**
     * @param float $avgPrice
     * @return Taxes
     */
    public function setAvgPrice(float $avgPrice) : self
    {
        $this->avgPrice = $avgPrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getAvgPrice() : float
    {
        return $this->avgPrice;
    }

    /**
     * @return float
     */
    public function getLoss() : float
    {
        return $this->loss;
    }

    /**
     * @param float $loss
     * @return Taxes
     */
    public function setLoss(float $loss) : self
    {
        $this->loss = $loss;
        return $this;
    }

    /**
     * @param int $position
     * @param array $transaction
     * @return float
     */
    public function calcAvgPrice(int $position, array $transaction) : float
    {
        if ($position = 0) {
            return 0.0;
        }

        return (
            ($position * $this->getAvgPrice()) +
            ($transaction['quantity'] * $transaction['unit-cost'])
        ) / ($position + $transaction['quantity']);
    }

    /**
     * @param array $transaction
     * @return float
     */
    public function getOperationProfit(array $transaction) : float
    {
        return ($transaction['unit-cost'] - $this->getAvgPrice()) * $transaction['quantity'];
    }
}