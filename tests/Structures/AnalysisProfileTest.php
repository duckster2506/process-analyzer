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

    public function testCanWriteRecordAndSaveRecordMemoryUsage(): void
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
        $uid = $obj->write();
        // Get memory usage for writing Record
        $usage = memory_get_usage() - $start;

        Utils::rawLog($obj);
        // Check if Profile's mem footprint and usage is correct
        $this->assertSame($usage, $obj->getUsage() - $initUsage);
        $this->assertSame($usage, $obj->getMemoryFootprints()[$uid]);
        // Type of $uid
        $this->assertIsString($uid);
        // Records size is now 1
        $this->assertCount(1, $obj->getRecords());
    }

    public function testCanWriteRecordsAndSaveMemoryFootprintAfterEachWritingProcess(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile");
        // Get init memory usage
        $totalUsage = $obj->getUsage();
        // Init Records size is 0
        $this->assertEmpty($obj->getRecords());

        for ($i = 0; $i < 100; $i++) {
            // Get memory before writing
            $start = memory_get_usage();
            // Write a Record
            $uid = $obj->write();
            // Get memory usage for writing Record
            $usage = memory_get_usage() - $start;

            // Add usage to $initUsage to get total usage
            $totalUsage += $usage;

            // Check if Profile's mem footprint is correct
            $this->assertSame($usage, $obj->getMemoryFootprints()[$uid]);
            // Check if Profile's total usage is correct
            $this->assertSame($totalUsage, $obj->getUsage());
            // Type of $uid
            $this->assertIsString($uid);
            // Records size is now 1
            $this->assertCount($i + 1, $obj->getRecords());
        }
    }

    public function testCanBeGottenByUid(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile");
        // Write a Record
        $uid = $obj->write();

        // Check if list of Records has key with value of [$uid]
        $this->assertArrayHasKey($uid, $obj->getRecords());
        // Check if both getting method point to same object
        $this->assertSame($obj->get($uid), $obj->getRecords()[$uid]);
    }

    public function testCanCloseRecord(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile");

        // Write a Record
        $uid = $obj->write();
        // Save the reference of Record
        $ref = $obj->get($uid);

        // Check if Record's $startTime and $endTime is equal since it was created recently (may vary by a few ms)
        $this->assertSame(floor($obj->get($uid)->getStartTime()), floor($obj->get($uid)->getEndTime()));

        // Sleep for 1s
        sleep(1);
        // Close
        $obj->close($uid);

        // Check if Record's $startTime and $endTime is not equal
        $this->assertNotSame(floor($obj->get($uid)->getStartTime()), floor($obj->get($uid)->getEndTime()));
        // Check if the closed one is still the same one
        $this->assertSame($ref, $obj->get($uid));
    }
}
