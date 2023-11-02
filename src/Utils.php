<?php

namespace Duckster\Analyzer;

class Utils
{
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
}
