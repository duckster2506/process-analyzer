<?php

namespace Duckstery\Analyzer\Tests\Config;

use Duckstery\Analyzer\AnalyzerConfig;

class RawPrintHideUIDConfig extends AnalyzerConfig
{
    protected bool $prettyPrint = false;

    protected bool $showUid = false;
}
