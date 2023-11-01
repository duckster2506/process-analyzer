<?php

namespace Duckster\Analyzer\Tests\Config;

use Duckster\Analyzer\AnalyzerConfig;

class UseFileFalseConfig extends AnalyzerConfig
{
    protected string|false $useFile = false;
}
