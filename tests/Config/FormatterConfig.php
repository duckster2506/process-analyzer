<?php

namespace Duckstery\Analyzer\Tests\Config;

use Duckstery\Analyzer\AnalyzerConfig;
use Duckstery\Analyzer\Tests\AnalyzerConfigTest;

class FormatterConfig extends AnalyzerConfig
{
    protected mixed $timeFormatter = [AnalyzerConfigTest::class, "addPrefix"];

    protected mixed $memFormatter = [AnalyzerConfigTest::class, "addPrefix"];
}
