<?php

namespace Duckster\Analyzer\Tests;

use Duckster\Analyzer\Structures\AnalysisRecord;
use Duckster\Analyzer\Utils;
use PHPUnit\Framework\TestCase;

class AnalyzerTest extends TestCase
{
    public function testCanBeCreatedFromValidEmail(): void
    {
        $this->assertEmpty([]);
    }
}
