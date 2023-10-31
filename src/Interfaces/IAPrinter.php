<?php

namespace Duckster\Analyzer\Interfaces;

abstract class IAPrinter
{
    public abstract function printProfile(IAProfile $profile): void;

    public function onPreprocessProfile(IAProfile $profile): void
    {
    }

    public function onPreprocessRecord(IARecord $record): void
    {
    }

    public function onEachRecordString(string $content): void
    {
    }

    public function onPrintProfileString(string $content): void
    {
    }
}
