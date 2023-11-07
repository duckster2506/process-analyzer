<?php

namespace Duckstery\Analyzer\Tests\Config;

use Duckstery\Analyzer\AnalyzerConfig;

class UseFileFalseConfig extends AnalyzerConfig
{
    protected string|false $useFile = false;
}
