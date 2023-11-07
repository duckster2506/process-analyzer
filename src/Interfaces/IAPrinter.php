<?php

namespace Duckstery\Analyzer\Interfaces;

abstract class IAPrinter
{
    public abstract function printProfile(IAProfile $profile): void;

    // ***************************************
    // Printer's hooks
    // ***************************************

    /**
     * Before modifying Profile
     *
     * @param IAProfile $profile
     * @return void
     */
    public function onPreprocessProfile(IAProfile $profile): void
    {
    }

    /**
     * Before modifying Record
     *
     * @param IARecord $record
     * @return void
     */
    public function onPreprocessRecord(IARecord $record): void
    {
    }

    /**
     * After converting Record to String
     * @param string $content
     * @return void
     */
    public function onEachRecordString(string $content): void
    {
    }

    /**
     * Before print Profile's statistic report
     *
     * @param string $content
     * @return void
     */
    public function onPrintProfileString(string $content): void
    {
    }
}
