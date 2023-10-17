<?php

namespace Duckster\Analyzer;

class Utils
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
}