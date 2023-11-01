<?php

namespace Duckster\Analyzer\Tests\Config;

use Duckster\Analyzer\AnalyzerConfig;

class OneLineConfig extends AnalyzerConfig
{
    protected bool $prettyPrint = false;

    protected bool $oneLine = true;
}
