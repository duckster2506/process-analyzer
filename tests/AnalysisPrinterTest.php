<?php

namespace Duckster\Analyzer\Tests;

use Duckster\Analyzer\AnalysisPrinter;
use Duckster\Analyzer\AnalyzerConfig;
use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\AnalyzerEntry;
use Duckster\Analyzer\Config;
use Duckster\Analyzer\Structures\AnalysisProfile;
use PHPUnit\Framework\TestCase;

class AnalysisPrinterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Analyzer::clear();
    }

    public function testCanBeConstructed(): void
    {
        // Create config
        $config = new Config();
        // Create printer
        $printer = new AnalysisPrinter($config);

        $printer->printProfile($this->getProfile());

        $this->assertTrue(true);
    }

    public function getProfile()
    {
        $uid1 = Analyzer::profile("Profile")->start("Record 1");

        $uid2 = Analyzer::profile("Profile")->start("Record 2");
        Analyzer::profile("Profile")->stop();

        $uid3 = Analyzer::profile("Profile")->start("Record 3");
        Analyzer::profile("Profile")->stop($uid1);

        Analyzer::profile("Profile")->stop($uid3);

        return Analyzer::getProfiles()["Profile"];
    }
}
