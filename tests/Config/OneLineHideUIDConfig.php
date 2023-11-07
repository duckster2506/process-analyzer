<?php

namespace Duckstery\Analyzer\Tests\Config;

use Duckstery\Analyzer\AnalyzerConfig;

class OneLineHideUIDConfig extends AnalyzerConfig
{
    protected bool $prettyPrint = false;

    protected bool $oneLine = true;

    protected bool $showUid = false;
}
