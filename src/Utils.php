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

    /**
     * Call hook
     *
     * @param mixed $obj
     * @param string $hookName
     * @param mixed ...$args
     * @return void
     */
    public static function callHook(mixed $obj, string $hookName, mixed ...$args): void
    {
        if (method_exists($obj, $hookName)) {
            call_user_func([$obj, $hookName], ...$args);
        }
    }

    /**
     * Apply formatter
     *
     * @param mixed $value
     * @param callable|null $formatter
     * @return string
     */
    public static function applyFormatter(mixed $value, mixed $formatter): string
    {
        if (is_callable($formatter)) {
            return $formatter($value);
        } else if (is_array($formatter)) {
            return call_user_func($formatter, $value);
        } else {
            return $value;
        }
    }
}
