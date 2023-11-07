<?php

namespace Duckstery\Analyzer\Tests\Config;

use Duckstery\Analyzer\AnalyzerConfig;

class InvalidProfileInstanceConfig extends AnalyzerConfig
{
    protected string $profile = AnalyzerConfig::class;
}
