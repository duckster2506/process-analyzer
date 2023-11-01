<?php

namespace Duckster\Analyzer\Tests;

use Duckster\Analyzer\AnalysisPrinter;
use Duckster\Analyzer\AnalyzerConfig;
use PHPUnit\Framework\TestCase;

class AnalyzerConfigTest extends TestCase
{
    private AnalyzerConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        // Create default config object
        $this->config = new AnalyzerConfig();
    }

    public function test_default_enable(): void
    {
        $this->assertTrue($this->config->enable());
    }

    public function test_default_defaultProfile(): void
    {
        $this->assertEquals("Default", $this->config->defaultProfile());
    }

    public function test_default_defaultRecordGetter(): void
    {
        $this->assertNull($this->config->defaultRecordGetter());
    }

    public function test_default_printer(): void
    {
        $this->assertEquals(AnalysisPrinter::class, $this->config->printer());
    }

    public function test_default_prettyPrint(): void
    {
        $this->assertTrue($this->config->prettyPrint());
    }

    public function test_default_oneLine(): void
    {
        $this->assertFalse($this->config->oneLine());
    }

    public function test_default_showUID(): void
    {
        $this->assertTrue($this->config->showUID());
    }

    public function test_default_useFile(): void
    {
        $this->assertEquals("logs/log.txt", $this->config->useFile());
    }

    public function test_default_useConsole(): void
    {
        $this->assertTrue($this->config->useConsole());
    }

    public function test_default_profilePrefix(): void
    {
        $this->assertEquals("", $this->config->profilePrefix());
    }

    public function test_default_profileSuffix(): void
    {
        $this->assertEquals("", $this->config->profileSuffix());
    }

    public function test_default_recordPrefix(): void
    {
        $this->assertEquals("", $this->config->recordPrefix());
    }

    public function test_default_recordSuffix(): void
    {
        $this->assertEquals("", $this->config->recordSuffix());
    }

    public function test_default_timeUnit(): void
    {
        $this->assertEquals("ms", $this->config->timeUnit());
    }

    public function test_default_timeFormatter(): void
    {
        // Try to convert ns to ms. Expect 5 ms
        $this->assertEquals("5 ms", $this->config->timeFormatter((5 * 1e+6)));
    }

    public function test_default_memUnit(): void
    {
        $this->assertEquals("KB", $this->config->memUnit());
    }

    public function test_default_memFormatter(): void
    {
        // Try to convert byte to kb. Expect 5 kb
        $this->assertEquals("5 KB", $this->config->memFormatter(5 * 1024));
    }

    public function test_default_topLeftChar(): void
    {
        $this->assertEquals("╭", $this->config->topLeftChar());
    }

    public function test_default_topRightChar(): void
    {
        $this->assertEquals("╮", $this->config->topRightChar());
    }

    public function test_default_bottomLeftChar(): void
    {
        $this->assertEquals("╰", $this->config->bottomLeftChar());
    }

    public function test_default_bottomRightChar(): void
    {
        $this->assertEquals("╯", $this->config->bottomRightChar());
    }

    public function test_default_topForkChar(): void
    {
        $this->assertEquals("┬", $this->config->topForkChar());
    }

    public function test_default_rightForkChar(): void
    {
        $this->assertEquals("┤", $this->config->rightForkChar());
    }

    public function test_default_bottomForkChar(): void
    {
        $this->assertEquals("┴", $this->config->bottomForkChar());
    }

    public function test_default_leftForkChar(): void
    {
        $this->assertEquals("├", $this->config->leftForkChar());
    }

    public function test_default_crossChar(): void
    {
        $this->assertEquals("┼", $this->config->crossChar());
    }

    public function test_default_horizontalLineChar(): void
    {
        $this->assertEquals("─", $this->config->horizontalLineChar());
    }

    public function test_default_verticalLineChar(): void
    {
        $this->assertEquals("│", $this->config->verticalLineChar());
    }
}
