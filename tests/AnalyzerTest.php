<?php

namespace Duckstery\Analyzer\Tests;

use Duckstery\Analyzer\Analyzer;
use Duckstery\Analyzer\AnalyzerConfig;
use Duckstery\Analyzer\Structures\AnalysisProfile;
use Duckstery\Analyzer\Tests\Config\DefaultRecordGetterConfig;
use Duckstery\Analyzer\Tests\Config\DisableConfig;
use Duckstery\Analyzer\Tests\Config\InvalidProfileInstanceConfig;
use PHPUnit\Framework\TestCase;

class AnalyzerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Analyzer::clear();
        Analyzer::tryToInit(new AnalyzerConfig());
    }

    public function testCanBeDisabled(): void
    {
        // Config
        Analyzer::tryToInit(new DisableConfig());
        // Create Profile
        $profile = Analyzer::profile("Profile");

        // No Profile is created
        $this->assertEmpty(Analyzer::getProfiles());
        // Return null
        $this->assertNull($profile);

        // Create Profile
        $profile = Analyzer::startProfile("Profile");
        // No Profile is created
        $this->assertEmpty(Analyzer::getProfiles());
        // Return null
        $this->assertNull($profile);
    }

    public function testCanTakeSnapshotBefore(): void
    {
        // Take snapshot
        $snapshot = Analyzer::takeSnapshot();
        $memAfterTakeSnapshot = memory_get_usage();

        // Check if snapshot is capture before it has been created (it means that it mem won't be included)
        $this->assertLessThan($memAfterTakeSnapshot, $snapshot['mem']);
        // Check if snapshot is an array
        $this->assertIsArray($snapshot);
        // Check if snapshot has 'time' and 'mem'
        $this->assertArrayHasKey('time', $snapshot);
        $this->assertArrayHasKey('mem', $snapshot);
    }

    public function testCanTakeSnapshotAfter(): void
    {
        // Take snapshot
        $snapshot = Analyzer::takeSnapshot(false);
        $memAfterTakeSnapshot = memory_get_usage();

        // Check if snapshot is capture after it has been created (it means that it mem will be included)
        $this->assertEquals($memAfterTakeSnapshot, $snapshot['mem']);
        // Check if snapshot is an array
        $this->assertIsArray($snapshot);
        // Check if snapshot has 'time' and 'mem'
        $this->assertArrayHasKey('time', $snapshot);
        $this->assertArrayHasKey('mem', $snapshot);
    }

    public function testCanConfig(): void
    {
        // Try to config Analyzer with a new config instance
        $config = new AnalyzerConfig();
        // Config
        Analyzer::tryToInit($config);

        // Check config type
        $this->assertInstanceOf(AnalyzerConfig::class, Analyzer::config());
        // Check if new config is used
        $this->assertSame($config, Analyzer::config());

        // Try to config with default config class
        Analyzer::tryToInit();

        // Check if Analyzer's config instance is still the same
        $this->assertSame($config, Analyzer::config());
    }

    public function testCanCreateProfile(): void
    {
        // Check if Analyzer's Profiles size is 0
        $this->assertEmpty(Analyzer::getProfiles());

        $name = "New profile has been created";
        // Create Profile
        Analyzer::profile($name);

        // Check if Analyzer's Profiles size is 1
        $this->assertCount(1, Analyzer::getProfiles());

        // Get Profile
        $profile = Analyzer::getProfiles()[$name];

        // Check if return an AnalysisProfile
        $this->assertInstanceOf(AnalysisProfile::class, $profile);
        // Check $profile name
        $this->assertSame($name, $profile->getName());
    }

    public function testCanReturnNullIfConfigWithInvalidProfileInstance(): void
    {
        Analyzer::tryToInit(new InvalidProfileInstanceConfig());

        // Return null
        $this->assertNull(Analyzer::profile("Some profile"));
    }

    public function testCanAddProfile(): void
    {
        // Check if Analyzer's Profiles size is 0
        $this->assertCount(0, Analyzer::getProfiles());

        $name = "Create profile by add";
        // Create Profile
        $profile = AnalysisProfile::create($name);
        // Add Profile
        Analyzer::addProfile($profile);

        // Check if Analyzer's Profiles size is 1
        $this->assertCount(1, Analyzer::getProfiles());
        // Check Analyzer's Profiles name
        $this->assertSame($name, Analyzer::getProfiles()[$name]->getName());
        // Return false if try to add same Profile
        $this->assertFalse(Analyzer::addProfile($profile));
    }

    public function testCanPopProfile(): void
    {
        $name = "Create profile by add";
        // Add Profile
        Analyzer::addProfile(AnalysisProfile::create($name));
        // Check if Analyzer's Profiles size is 1
        $this->assertCount(1, Analyzer::getProfiles());

        // Pop Profile with $name
        $profile = Analyzer::popProfile($name);

        // Check if Analyzer's Profiles size is 0
        $this->assertCount(0, Analyzer::getProfiles());
        // Check if the returned Profile's name is $name
        $this->assertSame($name, $profile->getName());
    }

    public function testCanClearProfile(): void
    {
        // Create new Profile
        Analyzer::profile("Recently added");
        Analyzer::addProfile(AnalysisProfile::create("Another"));

        // Check if Analyzer's Profiles size is 2
        $this->assertCount(2, Analyzer::getProfiles());

        // Clear Profiles
        Analyzer::clear();

        // Check if Analyzer's Profiles size is 0
        $this->assertEmpty(Analyzer::getProfiles());
    }

    public function testCanStartProfileRecording(): void
    {
        // Check if Analyzer's Profiles size is 0
        $this->assertEmpty(Analyzer::getProfiles());

        // Start recording
        $uid = Analyzer::startProfile("Default", "Testing");

        // Check if Analyzer will auto create a Profile if it wasn't created
        $this->assertCount(1, Analyzer::getProfiles());
        // Check Profile name
        $this->assertSame("Default", Analyzer::getProfiles()["Default"]->getName());
        // Check Profile has 1 Record
        $this->assertCount(1, Analyzer::getProfiles()["Default"]->getRecords());
        // Check Record name is Testing
        $this->assertSame("Testing", Analyzer::getProfiles()["Default"]->get($uid)->getName());
        // Check if Record is not accidentally stopped after starting
        $this->assertFalse(Analyzer::getProfiles()["Default"]->get($uid)->isStopped());
    }

    public function testCanStopProfileRecording(): void
    {
        // Start recording
        $uid = Analyzer::startProfile("Test stop", "Record that will be stopped");
        // Stop
        Analyzer::stopProfile("Test stop", $uid);
        // Check if "Test stop" Profile's record is stopped
        $this->assertTrue(Analyzer::getProfiles()["Test stop"]->get($uid)->isStopped());

        // Start recording
        $uid = Analyzer::start("Record that will be stopped");
        // Stop
        Analyzer::stop($uid);
        // Check if "Test stop" Profile's record is stopped
        $this->assertTrue(Analyzer::getProfiles()["Default"]->get($uid)->isStopped());
    }

    /**
     * This scenario just for testing, that's why it's slow
     *
     * @return void
     */
    public function testCanStartMultipleRecordingForProfiles(): void
    {
        Analyzer::clear();
        // Check if Analyzer's Profiles size is 0
        $this->assertEmpty(Analyzer::getProfiles());

        for ($i = 0; $i < 5; $i++) {
            // Profile name
            $profileName = "Profile " . $i;

            for ($j = 0; $j < 5; $j++) {
                // Record name
                $recordName = "Testing " . $i . "-" . $j;

                // Start recording
                $uid = Analyzer::startProfile($profileName, $recordName);

                // Check Profile has +1 Record
                $this->assertCount($j + 1, Analyzer::getProfiles()[$profileName]->getRecords());
                // Check Record name is Testing
                $this->assertSame($recordName, Analyzer::getProfiles()[$profileName]->get($uid)->getName());
                // Check if Record is not accidentally stopped after starting
                $this->assertFalse(Analyzer::getProfiles()[$profileName]->get($uid)->isStopped());
            }

            // Check if Analyzer will auto create a Profile if it wasn't created
            $this->assertCount($i + 1, Analyzer::getProfiles());
            // Check Profile name
            $this->assertSame($profileName, Analyzer::getProfiles()[$profileName]->getName());
        }
    }

    public function testCanFlush()
    {
        $filename = "logs" . DIRECTORY_SEPARATOR . date('Y-m-d') . ".log";
        // Remove file
        if (file_exists($filename)) {
            unlink($filename);
        }
        // Try to flush without any Profile
        Analyzer::flush();

        // Not thing happen and file will not be printed
        $this->assertFalse(file_exists($filename));

        // Create 2 Profile
        Analyzer::profile("Another");
        $uid1 = Analyzer::profile("Profile")->start("Record 1");
        $uid2 = Analyzer::profile("Profile")->start("Record 2");
        Analyzer::profile("Profile")->stop();
        $uid3 = Analyzer::profile("Profile")->start("Record 3");
        Analyzer::profile("Profile")->stop($uid1);
        Analyzer::profile("Profile")->stop($uid3);
        // Flush
        Analyzer::flush("Profile");

        // File is printed
        $this->assertTrue(file_exists($filename));
        // Only "Profile" is flush and delete
        $this->assertArrayHasKey("Another", Analyzer::getProfiles());

        // Flush all
        Analyzer::flush();

        // All Profile is deleted
        $this->assertEmpty(Analyzer::getProfiles());
    }

    public function testCanNotFlushWhenDisabled(): void
    {
        $filename = "logs" . DIRECTORY_SEPARATOR . date('Y-m-d') . ".log";
        // Try to remove file
        if (file_exists($filename)) {
            unlink($filename);
        }

        // Create Profile
        $uid1 = Analyzer::profile("Profile")->start("Record 1");
        $uid2 = Analyzer::profile("Profile")->start("Record 2");
        Analyzer::profile("Profile")->stop();
        $uid3 = Analyzer::profile("Profile")->start("Record 3");
        Analyzer::profile("Profile")->stop($uid1);
        Analyzer::profile("Profile")->stop($uid3);

        // Config
        Analyzer::tryToInit(new DisableConfig());
        // Flush
        Analyzer::flush("Profile");

        // File is not printed since Analyzer is disabled
        $this->assertFalse(file_exists($filename));
        // Profile "Profile" is not flushed
        $this->assertArrayHasKey("Profile", Analyzer::getProfiles());
    }

    public function testCanGetLevelCallerOfCaller(): void
    {
        $uid1 = Analyzer::startProfile("Profile");
        $uid2 = Analyzer::startProfile("Profile", "Record 1");

        // Check Record's name
        $this->assertEquals("Function: testCanGetLevelCallerOfCaller", Analyzer::getProfiles()["Profile"]->get($uid1)->getName());
        $this->assertEquals("Record 1", Analyzer::getProfiles()["Profile"]->get($uid2)->getName());
    }

    public function testCanGetCallerWithConfig(): void
    {
        Analyzer::tryToInit(new DefaultRecordGetterConfig());
        $uid1 = Analyzer::start();
        $uid2 = Analyzer::start("Record 1");

        // Check Record's name
        $this->assertEquals("Hello world!", Analyzer::getProfiles()["Default"]->get($uid1)->getName());
        $this->assertEquals("Record 1", Analyzer::getProfiles()["Default"]->get($uid2)->getName());
    }

    public static function printHelloWorld(): string
    {
        return "Hello world!";
    }
}
