<?php
require_once('vendor/autoload.php');

use Vlad\JobTest\BinService;
use Vlad\JobTest\CommissionCalculator;
use Vlad\JobTest\ExchangeRateService;
use Vlad\JobTest\FileHandler;

$commissionCalculator = new CommissionCalculator(new ExchangeRateService(), new BinService());
$fileHandler = new FileHandler($commissionCalculator);

$filename = $argv[1];
$fileHandler->processTransactionFile($filename);
