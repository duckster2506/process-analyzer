<?php

namespace Duckster\Analyzer\Tests;

use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\Structures\AnalysisProfile;
use Duckster\Analyzer\Structures\AnalysisRecord;
use Duckster\Analyzer\AnalysisUtils;
use PHPUnit\Framework\TestCase;

class AnalyzerTest extends TestCase
{
    public function testCanCreatePreparedProfile(): void
    {
        // Check if Analyzer's Profiles size is 0
        $this->assertEmpty(Analyzer::getProfiles());

        $name = "New profile has been created";
        // Create Profile
        $profile = Analyzer::profile($name);

        // Check if Analyzer's Profiles size is 1
        $this->assertCount(1, Analyzer::getProfiles());
        // Check if return an AnalysisProfile
        $this->assertInstanceOf(AnalysisProfile::class, $profile);
        // Check $profile name
        $this->assertSame($name, $profile->getName());
        // Check if $profile is prepared
        $this->assertIsArray($profile->getSnapshot());
    }

    public function testCanNotCreateUnpreparedProfile(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Profile not found");

        Analyzer::profile("Unprepared", false);
    }

    public function testCanAddProfile(): void
    {
        // Check if Analyzer's Profiles size is 1 (because of previous test)
        $this->assertCount(1, Analyzer::getProfiles());

        $name = "Create profile by add";
        // Create Profile
        $profile = AnalysisProfile::create($name);
        // Add Profile
        Analyzer::addProfile($profile);

        // Check if Analyzer's Profiles size is 2
        $this->assertCount(2, Analyzer::getProfiles());
        // Check if Analyzer's Profiles name
        $this->assertSame($name, Analyzer::profile($name)->getName());
    }

    public function testCanGetProfile(): void
    {
        // Get Profile from first test case
        // At this point, Profile is prepared, so we have to unload it snapshot
        $profile = Analyzer::profile("New profile has been created", false)->prep(null);
        // Get Profile again
        $profile = Analyzer::profile("New profile has been created", false);

        // Check return type
        $this->assertInstanceOf(AnalysisProfile::class, $profile);
        // Check if unprepared
        $this->assertNull($profile->getSnapshot());

        // Prepare and get Profile from first test case
        $profile = Analyzer::profile("New profile has been created");

        // Check return type
        $this->assertInstanceOf(AnalysisProfile::class, $profile);
        // Check if prepared
        $this->assertIsArray($profile->getSnapshot());
    }

    public function testCanPopProfile(): void
    {
        // Check if Analyzer's Profiles size is 2
        $this->assertCount(2, Analyzer::getProfiles());

        $name = "Create profile by add";
        // Pop Profile with $name
        $profile = Analyzer::popProfile($name);

        // Check if Analyzer's Profiles size is 1
        $this->assertCount(1, Analyzer::getProfiles());
        // Check if the returned Profile's name is $name
        $this->assertSame($name, $profile->getName());
    }

    public function testCanClearProfile(): void
    {
        $name = "Recently added";
        // Create new Profile
        Analyzer::profile($name);

        // Check if Analyzer's Profiles size is 3
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
        $this->assertSame("Default", Analyzer::profile("Default")->getName());
        // Check Profile has 1 Record
        $this->assertCount(1, Analyzer::profile("Default")->getRecords());
        // Check Record name is Testing
        $this->assertSame("Testing", Analyzer::profile("Default")->get($uid)->getName());
        // Check if Record is not accidentally closed after starting
        $this->assertFalse(Analyzer::profile("Default")->get($uid)->isClosed());
    }

    public function testCanNotStopNonExistProfile(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Profile not found");

        // Stop recording with wrong profile
        Analyzer::stopProfile("Wrong Profile", "anything");
    }

    public function testCanStopProfileRecording(): void
    {
        // Start recording
        $uid = Analyzer::startProfile("Test stop", "Record that will be stopped");
        // Stop
        Analyzer::stopProfile("Test stop", $uid);
        // Check if "Test stop" Profile's record is stopped
        $this->assertTrue(Analyzer::profile("Test stop")->get($uid)->isClosed());
    }

    public function testCanStartMultipleRecordingForProfiles(): void
    {
        Analyzer::clear();
        // Check if Analyzer's Profiles size is 0
        $this->assertEmpty(Analyzer::getProfiles());

        for ($i = 0; $i < 10; $i++) {
            // Profile name
            $profileName = "Profile " . $i;

            for ($j = 0; $j < 10; $j++) {
                // Record name
                $recordName = "Testing " . $i . "-" . $j;

                // Start recording
                $uid = Analyzer::startProfile($profileName, $recordName);

                // Check Profile has 1 Record
                $this->assertCount($j + 1, Analyzer::profile($profileName)->getRecords());
                // Check Record name is Testing
                $this->assertSame($recordName, Analyzer::profile($profileName)->get($uid)->getName());
                // Check if Record is not accidentally closed after starting
                $this->assertFalse(Analyzer::profile($profileName)->get($uid)->isClosed());
            }

            // Check if Analyzer will auto create a Profile if it wasn't created
            $this->assertCount($i + 1, Analyzer::getProfiles());
            // Check Profile name
            $this->assertSame($profileName, Analyzer::profile($profileName)->getName());
        }
    }

    public function testCanUseSharedRecord(): void
    {
        Analyzer::clear();
        // Check if Analyzer's Profiles size is 0
        $this->assertEmpty(Analyzer::getProfiles());

        // Start
        $record = Analyzer::startShared(["Profile 1", "Profile 2", "Profile 3"], "Shared record");

        // Will return an AnalysisRecord
        $this->assertInstanceOf(AnalysisRecord::class, $record);
        // Record will be mark as isShared
        $this->assertTrue($record->isShared());
        // All Profiles will have the same Record in list
        $this->assertSame($record, Analyzer::profile("Profile 1")->get($record->getUID()));
        $this->assertSame($record, Analyzer::profile("Profile 2")->get($record->getUID()));
        $this->assertSame($record, Analyzer::profile("Profile 3")->get($record->getUID()));

        // Stop
        Analyzer::stopShared($record);

        // The Record will be mark as closed
        $this->assertTrue($record->isClosed());
        // All Profiles will still have the same Record in list
        $this->assertSame($record, Analyzer::profile("Profile 1")->get($record->getUID()));
        $this->assertSame($record, Analyzer::profile("Profile 2")->get($record->getUID()));
        $this->assertSame($record, Analyzer::profile("Profile 3")->get($record->getUID()));
    }

//    public function testCanBeFlushed()
//    {
//        $class = AnalysisRecord::class;
//        Utils::rawLog($class);
//    }
}
