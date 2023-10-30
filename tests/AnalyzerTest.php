<?php

namespace Duckster\Analyzer\Tests;

use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\Structures\AnalysisProfile;
use PHPUnit\Framework\TestCase;

class AnalyzerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Analyzer::clear();
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
    }

//    public function testCanStartMultipleRecordingForProfiles(): void
//    {
//        Analyzer::clear();
//        // Check if Analyzer's Profiles size is 0
//        $this->assertEmpty(Analyzer::getProfiles());
//
//        for ($i = 0; $i < 10; $i++) {
//            // Profile name
//            $profileName = "Profile " . $i;
//
//            for ($j = 0; $j < 10; $j++) {
//                // Record name
//                $recordName = "Testing " . $i . "-" . $j;
//
//                // Start recording
//                $uid = Analyzer::startProfile($profileName, $recordName);
//
//                // Check Profile has +1 Record
//                $this->assertCount($j + 1, Analyzer::getProfiles()[$profileName]->getRecords());
//                // Check Record name is Testing
//                $this->assertSame($recordName, Analyzer::getProfiles()[$profileName]->get($uid)->getName());
//                // Check if Record is not accidentally stopped after starting
//                $this->assertFalse(Analyzer::getProfiles()[$profileName]->get($uid)->isStopped());
//            }
//
//            // Check if Analyzer will auto create a Profile if it wasn't created
//            $this->assertCount($i + 1, Analyzer::getProfiles());
//            // Check Profile name
//            $this->assertSame($profileName, Analyzer::getProfiles()[$profileName]->getName());
//        }
//    }

//    public function testCanBeFlushed()
//    {
//        $class = AnalysisRecord::class;
//        Utils::rawLog($class);
//    }
}
