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

    public function testCanSetupRecordRelationForSingleProfile(): void
    {
        // Create $profile
        $profile = AnalysisProfile::create("Profile");
        // Create Record
        $record1 = AnalysisRecord::open("Record 1");
        $record2 = AnalysisRecord::open("Record 2");
        $record3 = AnalysisRecord::open("Record 3");
        $record4 = AnalysisRecord::open("Record 4");

        // $profile is now inactive
        $this->assertFalse($profile->isActive());

        // Put $record1 in, start it and stop it
        $profile->start($record1);
        $profile->stop($record1->getUID());
        // Put $record2 in, start it
        $profile->start($record2);

        // $profile is now active
        $this->assertTrue($profile->isActive());
        // 4 Records don't have any relation
        $this->assertEmpty($record1->getRelations());
        $this->assertEmpty($record2->getRelations());
        $this->assertEmpty($record3->getRelations());
        $this->assertEmpty($record4->getRelations());

        // Setup relation for $record2
        AnalysisProfile::setupRecordRelation($record3, [$profile]);
        // Stop $record1
        $profile->stop($record2->getUID());
        // Setup relation for $record3
        AnalysisProfile::setupRecordRelation($record4, [$profile]);

        // No relation is established for $record1 since it was stopped before setup
        $this->assertEmpty($record1->getRelations());

        // There is a relation that established between $record2 and $record3 (because $record2 is running)
        $this->assertCount(1, $record2->getRelations());
        $this->assertCount(1, $record3->getRelations());
        // Check if both has the same relation
        $this->assertSame($record2->getRelations()[0], $record3->getRelations()[0]);
        // Check if relation owner is $record 2
        $this->assertSame($record2, $record2->getRelations()[0]->getOwner());
        // Check if relation target is $record 3
        $this->assertSame($record3, $record2->getRelations()[0]->getTarget());

        // But there isn't any relation that established between $record1 and $record3 (because $record2 is stopped before setup $record4)
        $this->assertEmpty($record4->getRelations());
    }

    public function testCanSetupRecordRelationForMultipleProfile(): void
    {
        // Create multiple Profiles
        $profiles = [];
        // Create Record
        $record3 = AnalysisRecord::open("Record 3");

        for ($i = 0; $i < 10; $i++) {
            // Create Record
            $record1 = AnalysisRecord::open("Record 1");
            $record2 = AnalysisRecord::open("Record 2");

            // Create Profile
            $profile = AnalysisProfile::create("Profile $i");
            // Put $record1 in, start it and stop it
            $profile->start($record1);
            $profile->stop($record1->getUID());
            // Put $record2 in, start it
            $profile->start($record2);
            // Add Profile to list
            $profiles[] = $profile;
        }
        // Record 3 does not have any relation
        $this->assertEmpty($record3->getRelations());

        // Setup
        AnalysisProfile::setupRecordRelation($record3, $profiles);

        // Record 3 will have total of 10 relation
        $this->assertCount(10, $record3->getRelations());

        // For each Profile $i
        for ($i = 0; $i < 10; $i++) {
            // Get the $record1 of each Profile
            $record1 = $profiles[$i]->get(array_key_first($profiles[$i]->getRecords()));
            // Get the $record1 of each Profile
            $record2 = $profiles[$i]->get(array_key_last($profiles[$i]->getRecords()));

            // No relation is established for $record1 since it was stopped before setup
            $this->assertEmpty($record1->getRelations());

            // There is a relation that established between $record2 and $record3 (because $record2 is running)
            $this->assertCount(1, $record2->getRelations());
            // Check if both has the same relation
            $this->assertSame($record2->getRelations()[0], $record3->getRelations()[$i]);
            // Check if relation owner is $record 2
            $this->assertSame($record2, $record2->getRelations()[0]->getOwner());
            // Check if relation target is $record 3
            $this->assertSame($record3, $record2->getRelations()[0]->getTarget());
        }
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
        $record = $profile->start($record);

        // Type of $record
        $this->assertInstanceOf(AnalysisRecord::class, $record);
        // Records size is now 1
        $this->assertCount(1, $profile->getRecords());
        // Check if Record is started
        $this->assertTrue($record->isStarted());
        // $profile will be considered active
        $this->assertTrue($profile->isActive());
    }

    public function testCanStartRecordByUid(): void
    {
        // Create Profile
        $profile = AnalysisProfile::create("Profile");
        // Create Record
        $record = AnalysisRecord::open("Record to be started by UID");

        // Check if Record is not started
        $this->assertFalse($record->isStarted());
        // Check $profile's record list size
        $this->assertEmpty($profile->getRecords());

        // Start $record into $profile. By starting $record, we've put $record in $profile
        $record = $profile->put($record);
        // Start by UID
        $profile->startByUID($record->getUID());

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
