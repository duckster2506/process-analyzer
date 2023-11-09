<?php

namespace Duckstery\Analyzer\Tests\Config;

use Duckstery\Analyzer\AnalyzerConfig;
use Duckstery\Analyzer\Tests\AnalyzerEntryTest;

class DefaultRecordGetterConfig extends AnalyzerConfig
{
    protected mixed $defaultRecordGetter = [AnalyzerEntryTest::class, "printHelloWorld"];
}
