<?php

namespace Duckstery\Analyzer\Tests;

use Duckstery\Analyzer\Analyzer;
use Duckstery\Analyzer\AnalyzerConfig;
use Duckstery\Analyzer\Structures\AnalysisProfile;
use Duckstery\Analyzer\Tests\Config\DefaultRecordGetterConfig;
use Duckstery\Analyzer\Tests\Config\DisableConfig;
use PHPUnit\Framework\TestCase;

class AAnalyzerInitTest extends TestCase
{
    public function testTest_Analyzer_tryToInit(): void
    {
        // Analyzer will auto tryToInit if config is null
        $this->assertInstanceOf(AnalyzerConfig::class, Analyzer::config());
    }
}
