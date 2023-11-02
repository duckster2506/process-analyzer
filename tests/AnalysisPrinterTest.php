<?php

namespace Duckster\Analyzer\Tests;

use Duckster\Analyzer\AnalysisPrinter;
use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\AnalyzerConfig;
use Duckster\Analyzer\Interfaces\IAProfile;
use Duckster\Analyzer\Interfaces\IARecord;
use Duckster\Analyzer\Structures\AnalysisProfile;
use Duckster\Analyzer\Tests\Config\HideUIDConfig;
use Duckster\Analyzer\Tests\Config\Hook1Config;
use Duckster\Analyzer\Tests\Config\Hook2Config;
use Duckster\Analyzer\Tests\Config\OneLineConfig;
use Duckster\Analyzer\Tests\Config\OneLineHideUIDConfig;
use Duckster\Analyzer\Tests\Config\PrefixSuffixConfig;
use Duckster\Analyzer\Tests\Config\RawPrintConfig;
use Duckster\Analyzer\Tests\Config\RawPrintHideUIDConfig;
use Duckster\Analyzer\Tests\Config\UseFileFalseConfig;
use PHPUnit\Framework\TestCase;

class AnalysisPrinterTest extends TestCase
{
    public static $onPreprocessProfile = null;
    public static $onPreprocessRecord = null;
    public static $onEachRecordString = null;
    public static $onPrintProfileString = null;

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
            $data[] = str_pad((new AnalyzerConfig())->timeFormatter($record->actualTime()), 8, " ", STR_PAD_LEFT);
            $data[] = str_pad((new AnalyzerConfig())->memFormatter($record->actualMem()), 7, " ", STR_PAD_LEFT);
        }
        $expected = sprintf("" .
            "Profile --------------------" . PHP_EOL .
            "╭───────────────┬──────────┬──────────┬─────────╮" . PHP_EOL .
            "│ Uid           │ Name     │ Time     │ Memory  │" . PHP_EOL .
            "├───────────────┼──────────┼──────────┼─────────┤" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "╰───────────────┴──────────┴──────────┴─────────╯" . PHP_EOL .
            "----------------------------" . PHP_EOL,
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
            $data[] = str_pad((new AnalyzerConfig())->timeFormatter($record->actualTime()), 8, " ", STR_PAD_LEFT);
            $data[] = str_pad((new AnalyzerConfig())->memFormatter($record->actualMem()), 7, " ", STR_PAD_LEFT);
        }
        $expected = sprintf("" .
            "Profile --------------------" . PHP_EOL .
            "╭──────────┬──────────┬─────────╮" . PHP_EOL .
            "│ Name     │ Time     │ Memory  │" . PHP_EOL .
            "├──────────┼──────────┼─────────┤" . PHP_EOL .
            "│ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │" . PHP_EOL .
            "╰──────────┴──────────┴─────────╯" . PHP_EOL .
            "----------------------------" . PHP_EOL,
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
            $data[] = (new RawPrintConfig())->timeFormatter($record->actualTime());
            $data[] = (new RawPrintConfig())->memFormatter($record->actualMem());
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
            "----------------------------" . PHP_EOL,
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
            $data[] = (new RawPrintHideUIDConfig())->timeFormatter($record->actualTime());
            $data[] = (new RawPrintHideUIDConfig())->memFormatter($record->actualMem());
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
            "----------------------------" . PHP_EOL,
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
            $data[] = (new RawPrintHideUIDConfig())->timeFormatter($record->actualTime());
            $data[] = (new RawPrintHideUIDConfig())->memFormatter($record->actualMem());
        }
        $expected = sprintf("" .
            "Profile --------------------" . PHP_EOL .
            "[%s] %s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "[%s] %s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "[%s] %s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "----------------------------" . PHP_EOL,
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
            $data[] = (new RawPrintHideUIDConfig())->timeFormatter($record->actualTime());
            $data[] = (new RawPrintHideUIDConfig())->memFormatter($record->actualMem());
        }
        $expected = sprintf("" .
            "Profile --------------------" . PHP_EOL .
            "%s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "%s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "%s: Time ⇒ [%s]; Memory ⇒ [%s]" . PHP_EOL .
            "----------------------------" . PHP_EOL,
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
            $data[] = str_pad($config->timeFormatter($record->actualTime()), 8, " ", STR_PAD_LEFT);
            $data[] = str_pad($config->memFormatter($record->actualMem()), 7, " ", STR_PAD_LEFT);
        }
        $expected = sprintf("" .
            "%s --------------------" . PHP_EOL .
            "╭───────────────┬─────────────────────┬──────────┬─────────╮" . PHP_EOL .
            "│ Uid           │ Name                │ Time     │ Memory  │" . PHP_EOL .
            "├───────────────┼─────────────────────┼──────────┼─────────┤" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "│ %s │ %s │ %s │ %s │" . PHP_EOL .
            "╰───────────────┴─────────────────────┴──────────┴─────────╯" . PHP_EOL .
            "----------------------------------------" . PHP_EOL,
            ...$data
        );

        // Check result
        $this->assertEquals($expected, $this->getFileContent());
    }

    public function test_can_use_hook_for_prettyPrint(): void
    {
        // Config
        $config = new Hook1Config();
        Analyzer::tryToInit($config);
        // Create Profile
        $profile = $this->getProfile();
        // Create Printer
        $printer = new AnalysisPrinter();
        $printer->printProfile($profile);

        // Check if hook onPreprocessProfile is called
        $this->assertInstanceOf(IAProfile::class, self::$onPreprocessProfile);
        // Check if hook onPreprocessRecord is called
        $this->assertInstanceOf(IARecord::class, self::$onPreprocessRecord);
        // Check if hook onEachRecordString is not called since prettyPrint is enabled
        $this->assertNull(self::$onEachRecordString);
        // Check if hook onPrintProfileString is called
        $this->assertIsString(self::$onPrintProfileString);
    }

    public function test_can_use_hook_for_non_prettyPrint(): void
    {
        // Config
        $config = new Hook2Config();
        Analyzer::tryToInit($config);
        // Create Profile
        $profile = $this->getProfile();
        // Create Printer
        $printer = new AnalysisPrinter();
        $printer->printProfile($profile);

        // Check if hook onPreprocessProfile is called
        $this->assertInstanceOf(IAProfile::class, self::$onPreprocessProfile);
        // Check if hook onPreprocessRecord is called
        $this->assertInstanceOf(IARecord::class, self::$onPreprocessRecord);
        // Check if hook onEachRecordString is not called since prettyPrint is disabled
        $this->assertIsString(self::$onEachRecordString);
        // Check if hook onPrintProfileString is called
        $this->assertIsString(self::$onPrintProfileString);
    }

    public function getProfile(): AnalysisProfile
    {
        $uid1 = Analyzer::profile("Profile")->start("Record 1");
        $str1 = str_repeat(" ", 1024);
        $uid2 = Analyzer::profile("Profile")->start("Record 2");
        $str2 = str_repeat(" ", 1024);
        Analyzer::profile("Profile")->stop();
        $uid3 = Analyzer::profile("Profile")->start("Record 3");
        Analyzer::profile("Profile")->stop($uid1);
        $str3 = str_repeat(" ", 1024);
        Analyzer::profile("Profile")->stop($uid3);

        return Analyzer::getProfiles()["Profile"];
    }

    public function getFileContent(): string
    {
        return file_get_contents("logs/log.txt");
    }
}
