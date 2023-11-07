<?php

namespace Duckstery\Analyzer\Tests\Printers;

use Duckstery\Analyzer\AnalysisPrinter;
use Duckstery\Analyzer\Interfaces\IAProfile;
use Duckstery\Analyzer\Interfaces\IARecord;
use Duckstery\Analyzer\Tests\AnalysisPrinterTest;

class HookPrinter extends AnalysisPrinter
{
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
     * After preprocess Record
     *
     * @param array $data
     * @return void
     */
    public function onEachPreprocessedRecord(array $data): void
    {
        AnalysisPrinterTest::$onEachPreprocessedRecord = $data;
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