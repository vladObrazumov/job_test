<?php

namespace Vlad\JobTest\Types;

class Transaction
{
    private string $bin;

    private float $amount;

    private string $currency;

    public function __construct(string $bin, float $amount, string $currency)
    {
        $this->bin = $bin;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getBin(): string
    {
        return $this->bin;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public static function createFromArray(array $transactionData) : self
    {
        return new Transaction($transactionData['bin'], $transactionData['amount'], $transactionData['currency']);
    }
}
