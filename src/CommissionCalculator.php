<?php

namespace Vlad\JobTest;
use Vlad\JobTest\Types\Transaction;
use Decimal\Decimal;

class CommissionCalculator
{
    const EU_COUNTRIES = ['AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PO','PT','RO','SE','SI','SK'];

    private ExchangeRateService $exchangeRateService;

    private BinService $binService;
    public function __construct(ExchangeRateService $exchangeRateService, BinService $binService)
    {
        $this->exchangeRateService = $exchangeRateService;
        $this->binService = $binService;
    }

    public function calculateCommissions(array $transactions): array
    {
        $result = [];

        $bins = array_map(function (Transaction $transaction){
            return $transaction->getBin();
        }, $transactions);

        $countryCodes = $this->binService->getCountriesByBins($bins);

        /** @var Transaction $transaction */
        foreach ($transactions as $transaction){
            $amountFixed = $transaction->getAmount();

            $rate = $this->exchangeRateService->getRate($transaction->getCurrency());
            if ($transaction->getCurrency() !== 'EUR' or $rate > 0) {
                $amountFixed = $transaction->getAmount() / $rate;
            }

            $binCountry = $countryCodes[$transaction->getBin()];
            $commissionAmount = $amountFixed * (self::isEu($binCountry) ? 0.01 : 0.02);
            $commission = new Decimal((string) $commissionAmount, 20);
            $result[] = self::roundUp($commission);
        }

        return $result;
    }

    public static function isEu(string $alpha2CountryCode): bool
    {
        if(in_array($alpha2CountryCode, self::EU_COUNTRIES)){
            return true;
        }
        return false;
    }

    public static function roundUp(Decimal $value): Decimal
    {
        return $value->round(2, Decimal::ROUND_UP);
    }
}
