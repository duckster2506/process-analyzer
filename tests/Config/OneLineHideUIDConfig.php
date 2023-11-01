<?php

namespace Duckster\Analyzer\Tests\Config;

use Duckster\Analyzer\AnalyzerConfig;

class OneLineHideUIDConfig extends AnalyzerConfig
{
    protected bool $prettyPrint = false;

    protected bool $oneLine = true;

    protected bool $showUid = false;
}
