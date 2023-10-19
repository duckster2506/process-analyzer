<?php

namespace Duckster\Analyzer\Tests\Structures;

use Duckster\Analyzer\Structures\AnalysisProfile;
use Duckster\Analyzer\Structures\AnalysisRecord;
use Duckster\Analyzer\Utils;
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
        $this->assertIsInt($obj->getUsage());
        $this->assertIsArray($obj->getMemoryFootprints());
    }

    public function testCanWriteRecordWithCorrectName(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile");

        // Init Records size is 0
        $this->assertEmpty($obj->getRecords());

        // Write a Record
        $uid = $obj->write("New record");

        // Type of $uid
        $this->assertIsString($uid);
        // Records size is now 1
        $this->assertCount(1, $obj->getRecords());
        // Check Record's name
        $this->assertSame("New record", $obj->get($uid)->getName());
    }

    public function testCanSaveWrittenRecordMemoryUsage(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile");
        // Get instance init usage
        $initUsage = $obj->getUsage();
        // Init Records size is 0
        $this->assertEmpty($obj->getRecords());

        // Get memory before writing
        $start = memory_get_usage();
        // Write a Record
        $uid = $obj->write("New record");
        // Get memory usage for writing Record
        $usage = memory_get_usage() - $start;

        // Check if Profile's mem footprint and usage is correct
        $this->assertSame($usage, $obj->getUsage() - $initUsage);
        $this->assertSame($usage, $obj->getMemoryFootprints()[$uid]);
    }

    public function testCanWriteRecords(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile");
        // Init Records size is 0
        $this->assertEmpty($obj->getRecords());

        for ($i = 0; $i < 100; $i++) {
            // Get Record's name
            $recordName = "Record " . $i;
            // Write a Record
            $uid = $obj->write($recordName);

            // Type of $uid
            $this->assertIsString($uid);
            // Records size is now 1
            $this->assertCount($i + 1, $obj->getRecords());
            // Check Record's name
            $this->assertSame($recordName, $obj->get($uid)->getName());
        }
    }

    public function testCanSaveMemoryFootprintAfterEachWritingProcess(): void{
        // Create instance
        $obj = AnalysisProfile::create("Profile");
        // Get init memory usage
        $totalUsage = $obj->getUsage();
        // Init Records size is 0
        $this->assertEmpty($obj->getRecords());

        for ($i = 0; $i < 100; $i++) {
            // Get Record's name
            $recordName = "Record " . $i;
            // Get memory before writing
            $start = memory_get_usage();
            // Write a Record
            $uid = $obj->write($recordName);
            // Get memory usage for writing Record
            $usage = memory_get_usage() - $start;

            // Add usage to $initUsage to get total usage
            $totalUsage += $usage;

            // Check if Profile's mem footprint is correct
            $this->assertSame($usage, $obj->getMemoryFootprints()[$uid]);
            // Check if Profile's total usage is correct
            $this->assertSame($totalUsage, $obj->getUsage());
        }
    }

    public function testCanBeGottenByUid(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile");
        // Write a Record
        $uid = $obj->write("Record");

        // Check if list of Records has key with value of [$uid]
        $this->assertArrayHasKey($uid, $obj->getRecords());
        // Check if both getting method point to same object
        $this->assertSame($obj->get($uid), $obj->getRecords()[$uid]);
        // Check if Record's UID is $uid
        $this->assertSame($uid, $obj->get($uid)->getUID());

        // Check return null if non-exist
        $this->assertNull($obj->get("123456789987654321"));
    }

    public function testCanCloseRecord(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile");

        // Write a Record
        $uid = $obj->write("Record");
        // Save the reference of Record
        $ref = $obj->get($uid);

        // Check if Record's $startTime and $endTime is equal since it was created recently (may vary by a few ms)
        $this->assertSame(floor($obj->get($uid)->getStartTime()), floor($obj->get($uid)->getEndTime()));

        // Sleep for 1s
        sleep(1);
        // Close
        $closed = $obj->stop($uid);

        // Check if Record's $startTime and $endTime is not equal
        $this->assertNotSame(floor($obj->get($uid)->getStartTime()), floor($obj->get($uid)->getEndTime()));
        // Check if the closed one is still the same one
        $this->assertSame($ref, $obj->get($uid));


        // Check isClosed flag
        $this->assertTrue($closed->isClosed());
    }

    public function testCanReturnRecordAfterClose(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile");
        // Write a Record
        $uid = $obj->write("Record");
        // Close
        $closed = $obj->stop($uid);

        // Check if $this->stop() return a AnalysisRecord
        $this->assertInstanceOf(AnalysisRecord::class, $closed);
        // Check if $this->stop() some non-exist UID will return null
        $this->assertNull($obj->stop("123546879987654321"));
    }
}
