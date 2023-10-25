<?php

namespace Duckster\Analyzer;

class AnalysisUtils
{
    /**
     * Log raw string
     *
     * @param string $data
     * @return void
     */
    public static function rawLog(string $data): void
    {
        file_put_contents("logs/" . date('Y-m-d') . ".log", $data . PHP_EOL, FILE_APPEND);
    }

    /**
     * Generate a snapshot stat
     *
     * @return array
     */
    public static function takeSnapshot(): array
    {
        return [
            'time' => hrtime(true),
            'mem' => memory_get_usage()
        ];
    }
}
