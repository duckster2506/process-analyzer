<?php

namespace Duckster\Analyzer\Tests\Config;

use Duckster\Analyzer\AnalyzerConfig;
use Duckster\Analyzer\Tests\AnalyzerConfigTest;

class FormatterConfig extends AnalyzerConfig
{
    protected mixed $timeFormatter = [AnalyzerConfigTest::class, "addPrefix"];

    protected mixed $memFormatter = [AnalyzerConfigTest::class, "addPrefix"];
}
