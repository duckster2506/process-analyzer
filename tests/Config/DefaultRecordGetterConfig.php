<?php

namespace Duckstery\Analyzer\Tests\Config;

use Duckstery\Analyzer\AnalyzerConfig;
use Duckstery\Analyzer\Tests\AnalyzerTest;

class DefaultRecordGetterConfig extends AnalyzerConfig
{
    protected mixed $defaultRecordGetter = [AnalyzerTest::class, "printHelloWorld"];
}
