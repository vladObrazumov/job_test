<?php
declare(strict_types=1);

use Decimal\Decimal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Vlad\JobTest\BinService;
use Vlad\JobTest\CommissionCalculator;
use Vlad\JobTest\ExchangeRateService;
use Vlad\JobTest\Types\Transaction;

final class CommissionCalculatorTest extends TestCase
{
    public function testIsEu(): void
    {
        $this->assertTrue(
            CommissionCalculator::isEu('AT')
        );
        $this->assertFalse(
            CommissionCalculator::isEu('CN')
        );
    }

    #[DataProvider('roundUpProvider')]
    public function testRoundUp(float $value, float $expected)
    {
        $this->assertEquals((new Decimal((string) $expected)), CommissionCalculator::roundUp(new Decimal((string) $value)));
    }

    public static function roundUpProvider(): array
    {
        return [
            [77.4, 77.4],
            [0.4618033988749895, 0.47],
            [73, 73],
            [73.1, 73.1],
            [73.9, 73.9],
            [0.468, 0.47],
            [77.91, 77.91],
            [0.411, 0.42],
            [0.499, 0.5],
            [0.491, 0.5],
            [0.4900000002, 0.5],
            [0.4900, 0.49],
            [77.00, 77],
            [0, 0],
        ];
    }

    public function testCalculateCommissions()
    {
        $exchangeRateServiceMock = $this->createMock(ExchangeRateService::class);
        $map = [
            ['EUR', 1],
            ['USD', 1.068913],
            ['JPY', 141.944141],
            ['GBP', 0.884333],
        ];
        $exchangeRateServiceMock->method("getRate")
            ->will($this->returnValueMap($map));


        $binServiceMock = $this->createMock(BinService::class);
        $binServiceMock->method('getCountriesByBins')->willReturn([
            45717360 => "DK",
            516793 => "LT",
            45417360 => "JP",
            41417360 => "US",
            4745030 => "GB",
        ]);

        $commissionCalculator = new CommissionCalculator($exchangeRateServiceMock, $binServiceMock);

        $this->assertEquals([
            new Decimal((string) 1.0),
            new Decimal((string) 2.0),
            new Decimal((string) 5.56),
            new Decimal((string) 0.47),
            new Decimal((string) 0.94),
            new Decimal((string) 1.88),
            new Decimal((string) 1.41),
            new Decimal((string) 2.44),
            new Decimal((string) 45.24),
            new Decimal((string) 22.62),
        ], $commissionCalculator->calculateCommissions([
            new Transaction("45717360",100.00,"EUR"),
            new Transaction("45717360",200.00,"EUR"),
            new Transaction("45717360",555.55,"EUR"),
            new Transaction("516793",50.00,"USD"),
            new Transaction("516793",100.00,"USD"),
            new Transaction("45417360",100.00,"USD"),
            new Transaction("45417360",10000.00,"JPY"),
            new Transaction("41417360",130.00,"USD"),
            new Transaction("4745030",2000.00,"GBP"),
            new Transaction("516793",2000.00,"GBP"),
        ]));
    }
}