<?php

namespace Duckster\Analyzer\Tests;

use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\AnalyzerConfig;
use Duckster\Analyzer\Structures\AnalysisProfile;
use Duckster\Analyzer\Tests\Config\DefaultRecordGetterConfig;
use Duckster\Analyzer\Tests\Config\DisableConfig;
use PHPUnit\Framework\TestCase;

class AAnalyzerInitTest extends TestCase
{
    public function testTest_Analyzer_tryToInit(): void
    {
        // Analyzer will auto tryToInit if config is null
        $this->assertInstanceOf(AnalyzerConfig::class, Analyzer::config());
    }
}
