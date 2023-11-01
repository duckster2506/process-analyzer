<?php

namespace Duckster\Analyzer\Tests\Config;

use Duckster\Analyzer\AnalyzerConfig;

class PrefixSuffixConfig extends AnalyzerConfig
{
    /**
     * @var string Profile prefix
     */
    protected string $profilePrefix = "Profile = [";

    /**
     * @var string Profile suffix
     */
    protected string $profileSuffix = "]";

    /**
     * @var string Record prefix
     */
    protected string $recordPrefix = "Record = (";

    /**
     * @var string Record suffix
     */
    protected string $recordSuffix = ")";
}
