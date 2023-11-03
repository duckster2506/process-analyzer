<?php

namespace Duckster\Analyzer\Tests\Config;

use Duckster\Analyzer\AnalyzerConfig;

class InvalidProfileInstanceConfig extends AnalyzerConfig
{
    protected string $profile = AnalyzerConfig::class;
}
