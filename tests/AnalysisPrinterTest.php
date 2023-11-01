<?php

namespace Duckster\Analyzer\Tests;

use Duckster\Analyzer\AnalysisPrinter;
use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\AnalyzerConfig;
use Duckster\Analyzer\Structures\AnalysisProfile;
use Duckster\Analyzer\Tests\Config\HideUIDConfig;
use Duckster\Analyzer\Tests\Config\OneLineConfig;
use Duckster\Analyzer\Tests\Config\OneLineHideUIDConfig;
use Duckster\Analyzer\Tests\Config\PrefixSuffixConfig;
use Duckster\Analyzer\Tests\Config\RawPrintConfig;
use Duckster\Analyzer\Tests\Config\RawPrintHideUIDConfig;
use Duckster\Analyzer\Tests\Config\UseFileFalseConfig;
use PHPUnit\Framework\TestCase;

class AnalysisPrinterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Re-init
        Analyzer::tryToInit(new AnalyzerConfig());
        // Clear Profiles
        Analyzer::clear();
        // Clear file
        file_put_contents("logs/log.txt", "");
    }

    public function test_can_use_constructor(): void
    {
        // Create printer
        $printer = new AnalysisPrinter();

        $this->assertInstanceOf(AnalysisPrinter::class, $printer);
    }

    public function test_can_use_printProfile_useFileFalse(): void
    {
        // Config
        Analyzer::tryToInit(new UseFileFalseConfig());
        // Create Profile
        $profile = $this->getProfile();
        // Create Printer
        $printer = new AnalysisPrinter();
        $printer->printProfile($profile);

        // Check result
        $this->assertEmpty($this->getFileContent());
    }

    public function test_can_use_printProfile_prettyPrint(): void
    {
        // Create Profile
        $profile = $this->getProfile();
        // Create Printer
        $printer = new AnalysisPrinter();
        $printer->printProfile($profile);

        // Expected
        // Iterate through each profile to get data
        $data = [];
        foreach ($profile->getRecords() as $record) {
            $data[] = $record->getUID();
            $data[] = $record->getName();
            $data[] = str_pad((new AnalyzerConfig())->timeFormatter($record->diffTime()), 8, " ", STR_PAD_LEFT);
            $data[] = str_pad((new AnalyzerConfig())->memFormatter($record->diffMem()), 8, " ", STR_PAD_LEFT);
        }
        $expected = sprintf("" .
            "Profile --------------------" . PHP_EOL .
            "╭───────────────┬──────────┬──────────┬──────────╮" . PHP_EOL .
            "│ Uid           │ Name     │ Time     │ Memory   │" . PHP_EOL .
            "├───────────────┼──────────┼──────────┼──────────┤" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "╰───────────────┴──────────┴──────────┴──────────╯" . PHP_EOL .
            "---------------------------" . PHP_EOL,
            ...$data
        );

        // Check result
        $this->assertEquals($expected, $this->getFileContent());
    }

    public function test_can_use_printProfile_prettyPrint_hideUID(): void
    {
        // Config
        Analyzer::tryToInit(new HideUIDConfig());
        // Create Profile
        $profile = $this->getProfile();
        // Create Printer
        $printer = new AnalysisPrinter();
        $printer->printProfile($profile);

        // Expected
        // Iterate through each profile to get data
        $data = [];
        foreach ($profile->getRecords() as $record) {
            $data[] = $record->getName();
            $data[] = str_pad((new AnalyzerConfig())->timeFormatter($record->diffTime()), 8, " ", STR_PAD_LEFT);
            $data[] = str_pad((new AnalyzerConfig())->memFormatter($record->diffMem()), 8, " ", STR_PAD_LEFT);
        }
        $expected = sprintf("" .
            "Profile --------------------" . PHP_EOL .
            "╭──────────┬──────────┬──────────╮" . PHP_EOL .
            "│ Name     │ Time     │ Memory   │" . PHP_EOL .
            "├──────────┼──────────┼──────────┤" . PHP_EOL .
            "│ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │" . PHP_EOL .
            "╰──────────┴──────────┴──────────╯" . PHP_EOL .
            "---------------------------" . PHP_EOL,
            ...$data
        );

        // Check result
        $this->assertEquals($expected, $this->getFileContent());
    }

    public function test_can_use_printProfile_rawPrint(): void
    {
        // Config
        Analyzer::tryToInit(new RawPrintConfig());
        // Create Profile
        $profile = $this->getProfile();
        // Create Printer
        $printer = new AnalysisPrinter();
        $printer->printProfile($profile);

        // Expected
        // Iterate through each profile to get data
        $data = [];
        foreach ($profile->getRecords() as $record) {
            $data[] = $record->getUID();
            $data[] = $record->getName();
            $data[] = (new RawPrintConfig())->timeFormatter($record->diffTime());
            $data[] = (new RawPrintConfig())->memFormatter($record->diffMem());
        }
        $expected = sprintf("" .
            "Profile --------------------" . PHP_EOL .
            "[%s] %s:" . PHP_EOL .
            "	Time ⇒ [%s];" . PHP_EOL .
            "	Memory ⇒ [%s]" . PHP_EOL .
            "[%s] %s:" . PHP_EOL .
            "	Time ⇒ [%s];" . PHP_EOL .
            "	Memory ⇒ [%s]" . PHP_EOL .
            "[%s] %s:" . PHP_EOL .
            "	Time ⇒ [%s];" . PHP_EOL .
            "	Memory ⇒ [%s]" . PHP_EOL .
            "---------------------------" . PHP_EOL,
            ...$data
        );

        // Check result
        $this->assertEquals($expected, $this->getFileContent());
    }

    public function test_can_use_printProfile_rawPrint_hideUID(): void
    {
        // Config
        Analyzer::tryToInit(new RawPrintHideUIDConfig());
        // Create Profile
        $profile = $this->getProfile();
        // Create Printer
        $printer = new AnalysisPrinter();
        $printer->printProfile($profile);

        // Expected
        // Iterate through each profile to get data
        $data = [];
        foreach ($profile->getRecords() as $record) {
            $data[] = $record->getName();
            $data[] = (new RawPrintHideUIDConfig())->timeFormatter($record->diffTime());
            $data[] = (new RawPrintHideUIDConfig())->memFormatter($record->diffMem());
        }
        $expected = sprintf("" .
            "Profile --------------------" . PHP_EOL .
            "%s:" . PHP_EOL .
            "	Time ⇒ [%s];" . PHP_EOL .
            "	Memory ⇒ [%s]" . PHP_EOL .
            "%s:" . PHP_EOL .
            "	Time ⇒ [%s];" . PHP_EOL .
            "	Memory ⇒ [%s]" . PHP_EOL .
            "%s:" . PHP_EOL .
            "	Time ⇒ [%s];" . PHP_EOL .
            "	Memory ⇒ [%s]" . PHP_EOL .
            "---------------------------" . PHP_EOL,
            ...$data
        );

        // Check result
        $this->assertEquals($expected, $this->getFileContent());
    }

    public function test_can_use_printProfile_rawPrint_oneLine(): void
    {
        // Config
        Analyzer::tryToInit(new OneLineConfig());
        // Create Profile
        $profile = $this->getProfile();
        // Create Printer
        $printer = new AnalysisPrinter();
        $printer->printProfile($profile);

        // Expected
        // Iterate through each profile to get data
        $data = [];
        foreach ($profile->getRecords() as $record) {
            $data[] = $record->getUID();
            $data[] = $record->getName();
            $data[] = (new RawPrintHideUIDConfig())->timeFormatter($record->diffTime());
            $data[] = (new RawPrintHideUIDConfig())->memFormatter($record->diffMem());
        }
        $expected = sprintf("" .
            "Profile --------------------" . PHP_EOL .
            "[%s] %s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "[%s] %s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "[%s] %s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "---------------------------" . PHP_EOL,
            ...$data
        );

        // Check result
        $this->assertEquals($expected, $this->getFileContent());
    }

    public function test_can_use_printProfile_rawPrint_oneLine_hideUID(): void
    {
        // Config
        Analyzer::tryToInit(new OneLineHideUIDConfig());
        // Create Profile
        $profile = $this->getProfile();
        // Create Printer
        $printer = new AnalysisPrinter();
        $printer->printProfile($profile);

        // Expected
        // Iterate through each profile to get data
        $data = [];
        foreach ($profile->getRecords() as $record) {
            $data[] = $record->getName();
            $data[] = (new RawPrintHideUIDConfig())->timeFormatter($record->diffTime());
            $data[] = (new RawPrintHideUIDConfig())->memFormatter($record->diffMem());
        }
        $expected = sprintf("" .
            "Profile --------------------" . PHP_EOL .
            "%s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "%s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "%s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "---------------------------" . PHP_EOL,
            ...$data
        );

        // Check result
        $this->assertEquals($expected, $this->getFileContent());
    }

    public function test_can_use_printProfile_prefixSuffix(): void
    {
        // Config
        $config = new PrefixSuffixConfig();
        Analyzer::tryToInit($config);
        // Create Profile
        $profile = $this->getProfile();
        // Create Printer
        $printer = new AnalysisPrinter();
        $printer->printProfile($profile);

        // Expected
        // Iterate through each profile to get data
        $data = [$config->profilePrefix() . "Profile" . $config->profileSuffix()];
        foreach ($profile->getRecords() as $record) {
            $data[] = $record->getUID();
            $data[] = $record->getName();
            $data[] = str_pad($config->timeFormatter($record->diffTime()), 8, " ", STR_PAD_LEFT);
            $data[] = str_pad($config->memFormatter($record->diffMem()), 8, " ", STR_PAD_LEFT);
        }
        $expected = sprintf("" .
            "%s --------------------" . PHP_EOL .
            "╭───────────────┬─────────────────────┬──────────┬──────────╮" . PHP_EOL .
            "│ Uid           │ Name                │ Time     │ Memory   │" . PHP_EOL .
            "├───────────────┼─────────────────────┼──────────┼──────────┤" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "╰───────────────┴─────────────────────┴──────────┴──────────╯" . PHP_EOL .
            "---------------------------------------" . PHP_EOL,
            ...$data
        );

        // Check result
        $this->assertEquals($expected, $this->getFileContent());
    }

    public function getProfile(): AnalysisProfile
    {
        $uid1 = Analyzer::profile("Profile")->start("Record 1");

        $uid2 = Analyzer::profile("Profile")->start("Record 2");
        Analyzer::profile("Profile")->stop();

        $uid3 = Analyzer::profile("Profile")->start("Record 3");
        Analyzer::profile("Profile")->stop($uid1);

        Analyzer::profile("Profile")->stop($uid3);

        return Analyzer::getProfiles()["Profile"];
    }

    public function getFileContent(): string
    {
        return file_get_contents("logs/log.txt");
    }
}
