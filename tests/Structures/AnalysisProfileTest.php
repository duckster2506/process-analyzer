<?php

namespace Duckster\Analyzer\Tests\Structures;

use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\Structures\AnalysisProfile;
use Duckster\Analyzer\Structures\AnalysisRecord;
use PHPUnit\Framework\TestCase;

class AnalysisProfileTest extends TestCase
{
    public function testCanBeConstructedAndGotten(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("New profile");

        // Is instance of AnalysisRecord
        $this->assertInstanceOf(AnalysisProfile::class, $obj);
        // Is data type correct
        $this->assertSame("New profile", $obj->getName());
        $this->assertIsArray($obj->getRecords());
    }

    public function testCanPutAndGetRecordById(): void
    {
        // Create instance
        $profile = AnalysisProfile::create("Profile");

        // Check $profile's record list size
        $this->assertEmpty($profile->getRecords());

        // Create a Record
        $record = AnalysisRecord::open("Record to be putted");
        // Put $record into $profile
        $profile->put($record);

        // Check size
        $this->assertCount(1, $profile->getRecords());
        // Get the Record by id and check if it's $record
        $this->assertSame($record, $profile->get($record->getUID()));
    }

    public function testCanStartRecord(): void
    {
        // Create Profile
        $profile = AnalysisProfile::create("Profile");
        // Create Record
        $record = AnalysisRecord::open("Record to be started");

        // Check if Record is not started
        $this->assertFalse($record->isStarted());
        // Check $profile's record list size
        $this->assertEmpty($profile->getRecords());

        // Start $record into $profile. By starting $record, we've put $record in $profile
        $record = $profile->start(AnalysisRecord::open("Record to be started"));

        // Type of $record
        $this->assertInstanceOf(AnalysisRecord::class, $record);
        // Records size is now 1
        $this->assertCount(1, $profile->getRecords());
        // Check if Record is started
        $this->assertTrue($record->isStarted());
        // $profile will be considered active
        $this->assertTrue($profile->isActive());
    }

    public function testCanStopRecord(): void
    {
        // Create Profile
        $profile = AnalysisProfile::create("Profile");
        // Create Record
        $record = AnalysisRecord::open("Record to be started");

        // Check if Record is not started or stopped
        $this->assertFalse($record->isStarted());
        $this->assertFalse($record->isStopped());

        // Start $record into $profile. By starting $record, we've put $record in $profile
        $record = $profile->start(AnalysisRecord::open("Record to be started"));

        // Type of $record
        $this->assertInstanceOf(AnalysisRecord::class, $record);
        // Check if Record is started
        $this->assertTrue($record->isStarted());

        // Stop $record
        $profile->stop($record->getUID());

        // Check if Record is stopped
        $this->assertTrue($record->isStopped());
        // $profile will be considered inactive
        $this->assertFalse($profile->isActive());
    }

    public function testCanStopSharedRecord(): void
    {
        // Create Profile
        $profile = AnalysisProfile::create("Profile");
        // Create Record
        $sharedRecord = AnalysisRecord::open("Shared record", true);

        // Check if Record is not started or stopped
        $this->assertFalse($sharedRecord->isStarted());
        $this->assertFalse($sharedRecord->isStopped());

        // Starting a shared Record will be the same as starting a non-shared Record
        $afterStart = $profile->start($sharedRecord);

        // It still the same Record
        $this->assertSame($sharedRecord, $afterStart);
        // Check if Record is started
        $this->assertTrue($sharedRecord->isStarted());

        // Stop $record
        $afterStop = $profile->stop($sharedRecord->getUID());

        // The returned Record will be different
        $this->assertNotSame($sharedRecord, $afterStop);
        // The returned will be considered stopped
        $this->assertTrue($afterStop->isStopped());
        // The original will still be started
        $this->assertTrue($sharedRecord->isStarted());
        // $profile will be considered inactive
        $this->assertFalse($profile->isActive());
    }

    public function testCanStartAndStopRecords(): void
    {
        // Create instance
        $profile = AnalysisProfile::create("Profile");
        // Init Records size is 0
        $this->assertEmpty($profile->getRecords());

        for ($i = 0; $i < 100; $i++) {
            // Get Record's name
            $recordName = "Record " . $i;
            // Create Record
            $record = AnalysisRecord::open($recordName);

            // Check if Record is not started
            $this->assertFalse($record->isStarted());

            // Start $record into $profile. By starting $record, we've put $record in $profile
            $record = $profile->start(AnalysisRecord::open($recordName));

            // Check if Record is started
            $this->assertTrue($record->isStarted());

            $profile->stop($record->getUID());

            // Check if Record is started
            $this->assertTrue($record->isStopped());
        }

        // Init Records size is 100
        $this->assertCount(100, $profile->getRecords());
    }
}
