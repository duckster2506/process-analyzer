<?php

namespace Duckster\Analyzer\Tests\Config;

use Duckster\Analyzer\AnalyzerConfig;
use Duckster\Analyzer\Interfaces\IAProfile;
use Duckster\Analyzer\Interfaces\IARecord;
use Duckster\Analyzer\Tests\AnalysisPrinterTest;

class Hook1Config extends AnalyzerConfig
{

    protected bool $prettyPrint = true;

    /**
     * Before modifying Profile
     *
     * @param IAProfile $profile
     * @return void
     */
    public function onPreprocessProfile(IAProfile $profile): void
    {
        AnalysisPrinterTest::$onPreprocessProfile = $profile;
    }

    /**
     * Before modifying Record
     *
     * @param IARecord $record
     * @return void
     */
    public function onPreprocessRecord(IARecord $record): void
    {
        AnalysisPrinterTest::$onPreprocessRecord = $record;
    }

    /**
     * After converting Record to String
     * @param string $content
     * @return void
     */
    public function onEachRecordString(string $content): void
    {
        AnalysisPrinterTest::$onEachRecordString = $content;
    }

    /**
     * Before print Profile's statistic report
     *
     * @param string $content
     * @return void
     */
    public function onPrintProfileString(string $content): void
    {
        AnalysisPrinterTest::$onPrintProfileString = $content;
    }
}
