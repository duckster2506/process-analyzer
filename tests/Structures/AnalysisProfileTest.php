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
        $obj = AnalysisProfile::create();

        // Is instance of AnalysisRecord
        $this->assertInstanceOf(AnalysisProfile::class, $obj);
        // Is data type correct
        $this->assertIsArray($obj->getRecords());
        $this->assertIsInt($obj->getUsage());
    }

    public function testCanWriteRecord(): void
    {
        // Create instance
        $obj = AnalysisProfile::create();
        // Init Records size is 0
        $this->assertEmpty($obj->getRecords());

        // Write a Record
        $uid = $obj->write();
        // Type of $uid
        $this->assertIsString($uid);
        // Records size is now 1
        $this->assertCount(1, $obj->getRecords());
    }

    public function testCanWriteRecords(): void
    {
        // Create instance
        $obj = AnalysisProfile::create();
        // Init Records size is 0
        $this->assertEmpty($obj->getRecords());

        for ($i = 0; $i < 100; $i++) {
            // Write a Record
            $uid = $obj->write();
            // Type of $uid
            $this->assertIsString($uid);
            // Records size is now 1
            $this->assertCount($i + 1, $obj->getRecords());
        }
    }

    public function testCanBeGottenByUid(): void
    {
        // Create instance
        $obj = AnalysisProfile::create();
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
        $obj = AnalysisProfile::create();

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
