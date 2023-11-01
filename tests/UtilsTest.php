<?php

namespace Duckster\Analyzer\Tests;

use Duckster\Analyzer\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    private $isHooked = false;

    public function test_can_use_callHook(): void
    {
        // Create config
        Utils::callHook($this, "onHook", "hello");

        $this->assertTrue($this->isHooked);

        // Try to call non-exist hook and expect no Exception
        Utils::callHook($this, "invalidHook", "world");
    }

    public function onHook(string $param): void
    {
        $this->isHooked = true;
        $this->assertEquals("hello", $param);
    }
}
