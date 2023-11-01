<?php

namespace Duckster\Analyzer\Tests\Config;

use Duckster\Analyzer\AnalyzerConfig;

class RawPrintHideUIDConfig extends AnalyzerConfig
{
    protected bool $prettyPrint = false;

    protected bool $showUid = false;
}
