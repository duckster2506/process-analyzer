<?php

namespace Duckster\Analyzer\Tests\Config;

use Duckster\Analyzer\AnalyzerConfig;

class InvalidRecordInstanceConfig extends AnalyzerConfig
{
    protected string $record = AnalyzerConfig::class;
}
