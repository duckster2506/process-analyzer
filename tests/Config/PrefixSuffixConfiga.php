<?php

namespace Duckster\Analyzer\Tests\Config;

use Duckster\Analyzer\AnalysisPrinter;
use Duckster\Analyzer\AnalyzerConfig;

class PrefixSuffixConfiga extends AnalyzerConfig
{
    // ***************************************
    // Analyzer
    // ***************************************

    /**
     * @var string Default Profile name
     */
    protected string $defaultProfile = "Default";

    /**
     * @var callable|array|null Default record name getter
     */
    protected mixed $defaultRecordGetter = null;

    // ***************************************
    // Printer
    // ***************************************

    /**
     * @var string Printer class
     */
    protected string $printer = AnalysisPrinter::class;

    /**
     * @var bool Print Profile as table
     */
    protected bool $prettyPrint = false;

    /**
     * @var bool Print string in one line
     */
    protected bool $oneLine = false;

    /**
     * @var bool Show UID
     */
    protected bool $showUid = true;

    /**
     * @var string|bool Print to file
     */
    protected string|bool $useFile = "logs/log.txt";

    /**
     * @var bool Print to console
     */
    protected bool $useConsole = true;

    // ***************************************
    // Modify Profile and Record
    // ***************************************

    /**
     * @var string Profile prefix
     */
    protected string $profilePrefix = "";

    /**
     * @var string Profile suffix
     */
    protected string $profileSuffix = "";

    /**
     * @var string Record prefix
     */
    protected string $recordPrefix = "";

    /**
     * @var string Record suffix
     */
    protected string $recordSuffix = "";

}
