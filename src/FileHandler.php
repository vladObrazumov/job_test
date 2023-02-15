<?php

namespace Vlad\JobTest;

use Decimal\Decimal;
use Vlad\JobTest\Types\Transaction;

class FileHandler
{
    const BATCH_SIZE = 500;
    private CommissionCalculator $commissionCalculator;

    public function __construct(CommissionCalculator $commissionCalculator)
    {
        $this->commissionCalculator = $commissionCalculator;
    }

    public function processTransactionFile(string $filename): void
    {
        $fileResource = fopen($filename, 'r');

        if (!$fileResource) {
            throw new \InvalidArgumentException("Can't read the file. Filename: " . $filename);
        }

        try {
            $batch = [];
            while (!feof($fileResource)) {
                $fileLine = fgets($fileResource);
                if (!$fileLine) {
                    throw new \UnexpectedValueException("Can't read the next line.");
                }

                $transaction = json_decode($fileLine, true);
                if ($transaction === null) {
                    throw new \UnexpectedValueException("Can't parse the json. Value: " . $fileLine);
                }

                $batch[] = Transaction::createFromArray($transaction);
                if (count($batch) < self::BATCH_SIZE) {
                    continue;
                }

                $commissions = $this->commissionCalculator->calculateCommissions($batch);
                $batch = [];

                self::echoCommissions($commissions);
            }

            $commissions = $this->commissionCalculator->calculateCommissions($batch);
            self::echoCommissions($commissions);

        }finally {
            fclose($fileResource);
        }
    }

    public static function echoCommissions(array $commissions) : void
    {
        /** @var Decimal $commission */
        foreach ($commissions as $commission) {
            echo $commission->toString() . "\n";
        }
    }
}
