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
    }

    public function test_can_use_applyFormatter(): void
    {
        $expected = "Hello World!";

        $this->assertEquals($expected, Utils::applyFormatter("Hello", fn($value) => $value . " World!"));
        $this->assertEquals($expected, Utils::applyFormatter("Hello", [$this, "addWorld"]));
    }

    public function onHook(string $param): void
    {
        $this->isHooked = true;
        $this->assertEquals("hello", $param);
    }

    public function addWorld(string $value): string
    {
        $this->assertEquals("Hello", $value);
        return $value . " World!";
    }
}
