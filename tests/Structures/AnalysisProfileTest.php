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
        $this->assertNull($obj->getSnapshot());
    }

    public function testCanBePrepared(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("New profile");

        // Check if Profile's snapshot is null
        $this->assertNull($obj->getSnapshot());

        // Create snapshot
        $snapshot = [
            'time' => hrtime(true),
            'mem' => memory_get_usage()
        ];
        // Prepare
        $afterPrepare = $obj->prep($snapshot);

        // Return AnalysisProfile
        $this->assertInstanceOf(AnalysisProfile::class, $afterPrepare);
        // Check getter
        $this->assertSame($snapshot, $afterPrepare->getSnapshot());
    }

    public function testMustPrepareBeforeWrite(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Profile is not ready yet");

        AnalysisProfile::create("Unprepared")->write("Hello");
    }

    public function testCanWriteRecordWithCorrectName(): void
    {
        // Take snapshot
        $snapshot = Analyzer::takeSnapshot();
        // Create instance
        $obj = AnalysisProfile::create("Profile")->prep($snapshot);

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
        // Check Record's preSnapshot
        $this->assertSame($snapshot, $obj->get($uid)->getPreSnapshot());
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
            $uid = $obj->prep(Analyzer::takeSnapshot())->write($recordName);

            // Type of $uid
            $this->assertIsString($uid);
            // Records size is now 1
            $this->assertCount($i + 1, $obj->getRecords());
            // Check Record's name
            $this->assertSame($recordName, $obj->get($uid)->getName());
        }
    }

    public function testCanBeGottenByUid(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile")->prep(Analyzer::takeSnapshot());
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
        $obj = AnalysisProfile::create("Profile")->prep(Analyzer::takeSnapshot());

        // Write a Record
        $uid = $obj->write("Record");
        // Save the reference of Record
        $ref = $obj->get($uid);

        // Since $record is created recently, check if startTime is not 0
        $this->assertNotEquals(0.0, $obj->get($uid)->getStartTime());
        // Since $record is created recently, check if endTime is 0
        $this->assertEquals(0.0, $obj->get($uid)->getEndTime());

        // Sleep for 1s
        sleep(1);
        // Close
        $closed = $obj->stop($uid);

        // Since $record is stopped recently, check if endTime is not 0
        $this->assertNotEquals(0.0, $obj->get($uid)->getEndTime());
        // Check if the closed one is still the same one
        $this->assertSame($ref, $obj->get($uid));


        // Check isClosed flag
        $this->assertTrue($closed->isClosed());
    }

    public function testCanReturnRecordAfterClose(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile")->prep(Analyzer::takeSnapshot());
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
