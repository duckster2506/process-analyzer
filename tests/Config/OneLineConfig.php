<?php

namespace Duckstery\Analyzer\Tests\Config;

use Duckstery\Analyzer\AnalyzerConfig;

class OneLineConfig extends AnalyzerConfig
{
    protected bool $prettyPrint = false;

    protected bool $oneLine = true;
}
