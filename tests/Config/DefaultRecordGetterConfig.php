<?php

namespace Duckster\Analyzer\Tests\Config;

use Duckster\Analyzer\AnalyzerConfig;
use Duckster\Analyzer\Tests\AnalyzerTest;

class DefaultRecordGetterConfig extends AnalyzerConfig
{
    protected mixed $defaultRecordGetter = [AnalyzerTest::class, "printHelloWorld"];
}
