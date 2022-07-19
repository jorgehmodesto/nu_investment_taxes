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

    const MIN_TRANSACTION_VALUE_TO_PAY_TAXES = 20000;

    /**
     * Tax constructor.
     * @param array $orders
     */
    public function __construct($orders = [])
    {
        $this->setOrders($orders);
    }

    /**
     * @return Taxes
     */
    public function calculate() : self
    {
        $taxes = array_map(function($order) {

            if (!in_array($order['operation'], [
                self::BUY_OPERATION, self::SELL_OPERATION
            ])) {
                $this->setError(
                    "Invalid operation ({$order['operation']}) - Transaction: " . json_encode($order)
                );
                return ["tax" => 0];
            }

            $position = $this->getPosition();

            if ($order['operation'] == self::BUY_OPERATION) {

                $this->calcAvgPrice($position, $order);
                $position += $order['quantity'];
                $this->setPosition($position);

                return ["tax" => 0];
            }

            $tax = 0.0;
            $result = $this->getTransactionResult($order);

            $loss = $this->getLoss();
            $result += $loss;
            $this->setLoss($result);

            if (
                ($order['quantity'] * $order['unit-cost']) > self::MIN_TRANSACTION_VALUE_TO_PAY_TAXES &&
                ($result + $this->getLoss()) > 0
            ) {
                $tax = round($result * self::TAX_PERCENTAGE, 2);
            }

            $position -= $order['quantity'];
            $this->setPosition($position);

            return ["tax" => $tax];

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

    /**
     * @param int $position
     * @return Taxes
     */
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
     * @param $orders
     * @return Taxes
     */
    public function setOrders($orders) : self
    {
        if (!is_array($orders)) {
            $orders = json_decode($orders, true);
        }

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
     * @param float $result
     * @return Taxes
     */
    public function setLoss(float $result) : self
    {
        if ($result > 0) {
            $result = 0;
        }

        $this->loss = $result;
        return $this;
    }

    /**
     * @param int $position
     * @param array $order
     * @return float
     */
    public function calcAvgPrice(int $position, array $order) : float
    {
        $avgPrice =  (
                ($position * $this->getAvgPrice()) + ($order['quantity'] * $order['unit-cost'])
            ) / ($position + $order['quantity']);

        $this->setAvgPrice($avgPrice);

        return $avgPrice;
    }

    /**
     * @param array $order
     * @return float
     */
    public function getTransactionResult(array $order) : float
    {
        return ($order['unit-cost'] - $this->getAvgPrice()) * $order['quantity'];
    }
}