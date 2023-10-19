<?php

namespace Duckster\Analyzer\Tests\Structures;

use PHPUnit\Framework\TestCase;
use Duckster\Analyzer\Structures\AnalysisRecord;

class AnalysisRecordTest extends TestCase
{
    public function testCanBeConstructedAndGotten(): void
    {
        // Create instance
        $obj = AnalysisRecord::open("New record");

        // Is instance of AnalysisRecord
        $this->assertInstanceOf(AnalysisRecord::class, $obj);
        // Is data type correct
        $this->assertSame("New record", $obj->getName());
        $this->assertIsString($obj->getUID());
        $this->assertIsFloat($obj->getStartTime());
        $this->assertIsFloat($obj->getEndTime());
        $this->assertIsInt($obj->getRealMem());
        $this->assertIsInt($obj->getStartEmMem());
        $this->assertIsInt($obj->getEndEmMem());
        $this->assertIsInt($obj->getEmPeak());
        $this->assertIsInt($obj->getRealPeak());
        $this->assertIsInt($obj->getUsage());
        $this->assertIsBool($obj->isShared());
    }

    public function testOpenSharedAndNonSharedRecord(): void
    {
        $this->assertFalse(AnalysisRecord::open("Non shared")->isShared());
        $this->assertTrue(AnalysisRecord::open("Shared", true)->isShared());
    }

    public function testCanBeStartedOnce(): void
    {
        // Create instance and start recording
        $obj = AnalysisRecord::open("Record");

        // Check isStarted flag
        $this->assertFalse($obj->isStarted());

        // Start
        $obj->start();
        // Get $start timestamp
        $start = $obj->getStartTime();

        // Check isStarted flag
        $this->assertTrue($obj->isStarted());

        // Sleep
        sleep(1);
        // Start
        $obj->start();

        // Check $start timestamp
        $this->assertSame($start, $obj->getStartTime());
    }

    public function testCanReturnSelfAfterStarted(): void
    {
        // Create instance and start recording
        $obj = AnalysisRecord::open("Record");

        $this->assertSame($obj, $obj->start());
    }

    public function testCanBeClosed(): void
    {
        // Create instance and start recording
        $obj = AnalysisRecord::open("Record")->start();

        // Check if Record's $startTime and $endTime is equal since it was created recently (may vary by a few ms)
        $this->assertLessThanOrEqual(1, floor($obj->getStartTime()) - floor($obj->getEndTime()));
        // Check if Record's $startEmMem and $endEmMem is equal since it was created recently
        $this->assertSame($obj->getStartEmMem(), $obj->getEndEmMem());

        // Sleep for 1s
        sleep(1);
        // Close
        $obj->close();

        // Check if Record's $startTime and $endTime is not equal
        $this->assertNotSame(floor($obj->getStartTime()), floor($obj->getEndTime()));
        // Check if Record's isClosed is true
        $this->assertTrue($obj->isClosed());
    }

    public function testCanReturnSelfAfterClose(): void
    {
        // Create instance and start recording
        $obj = AnalysisRecord::open("Record")->start();

        // Check if close return self
        $this->assertSame($obj, $obj->close());
    }

    public function testCanBeClosedOnce(): void
    {
        // Create instance and start recording
        $obj = AnalysisRecord::open("Record")->start();
        // Close
        $afterClose = $obj->close();

        // Check isClosed flag
        $this->assertTrue($obj->isClosed());
        // Check if return self
        $this->assertSame($obj, $afterClose);
        // Get end timestamp
        $end = $obj->getEndTime();

        // Sleep for 1 second
        sleep(1);
        // Close one more time
        $afterClose = $obj->close();
        // Check if return null
        $this->assertNull($afterClose);
        // Check if end timestamp is still intact
        $this->assertSame($end, $obj->getEndTime());
    }

    public function testCanCloseSharedRecordForSingleProfile(): void
    {
        // Create Record and start
        $obj = AnalysisRecord::open("Shared record", true)->start();
        // Request to close but not shared
        $afterClosed = $obj->close();

        // In this case, $obj and $afterClosed will be 2 different object
        $this->assertNotSame($obj, $afterClosed);
        // The original will still be open and shared
        $this->assertFalse($obj->isClosed());
        $this->assertTrue($obj->isShared());
        // The after closed will be closed and non-shared
        $this->assertTrue($afterClosed->isClosed());
        $this->assertFalse($afterClosed->isShared());
        // Both UID is different
        $this->assertNotSame($obj->getUID(), $afterClosed->getUID());
    }

    public function testCanCloseSharedRecordForAllProfile(): void
    {
        // Create Record and start
        $obj = AnalysisRecord::open("Shared record", true)->start();
        // Request to close shared
        $afterClosed = $obj->close(true);

        // In this case, $obj and $afterClosed will the same
        $this->assertSame($obj, $afterClosed);
        // The original will be closed and shared
        $this->assertTrue($obj->isClosed());
        $this->assertTrue($obj->isShared());
        // Both UID is the same
        $this->assertSame($obj->getUID(), $afterClosed->getUID());
    }

    public function testCanSaveOwnMemoryUsage(): void
    {
        // Memory before create an AnalysisRecord
        $start = memory_get_usage();
        // Create instance and start recording
        $obj = AnalysisRecord::open("Record")->start();
        // Memory after create an AnalysisRecord
        $end = memory_get_usage();

        // AnalysisRecord will save it own used memory
        $this->assertSame($end - $start, $obj->getUsage());
    }

    public function testCanFetchEndEmMemProperly(): void
    {
        // Memory before create an AnalysisRecord
        $start = memory_get_usage();
        // Create instance
        $obj = AnalysisRecord::open("Record")->start();
        // Close to get endEmMem
        $obj->close();
        // Memory after create an AnalysisRecord
        $end = memory_get_usage();
        // Calculate the allocated memory for $obj
        $mem = $end - $start;

        // Check if AnalysisRecord will always exclude it used memory out of endEmMem
        $this->assertSame($end, $obj->getEndEmMem() + $obj->getUsage());
        $this->assertSame($end - $mem, $obj->getEndEmMem());
    }

    public function testCanCalculateTimeDiff(): void
    {
        // Get the start of execution
        $start = (hrtime(true) / 1e+6);

        // Create instance
        $obj = AnalysisRecord::open("Record")->start();
        // Sleep for 3 second
        sleep(3);
        // Close Record
        $obj->close();

        $end = hrtime(true) / 1e+6;
        // floor() will only floor the interval to ms
        // It means that may vary by few 0.xx ms because of other operation
        $this->assertLessThanOrEqual(1, abs(floor($end - $start) - floor($obj->diffTime())));
    }

    public function testCanCalculateEmMemDiff(): void
    {
        // Before create string with 1024 characters
        $start = memory_get_usage();
        // Create String
        $str1 = str_repeat(" ", 1024);
        // After create string with 1024 characters
        $end = memory_get_usage();

        // Open Record
        $obj = AnalysisRecord::open("Record")->start();
        // Create String
        $str2 = str_repeat(" ", 1024);
        // Close Record
        $obj->close();

        // Check Record emMem diff
        $this->assertSame($end - $start, $obj->diffEmMem());
        // Just to make sure $str1 equals $str2 and both will be kept
        $this->assertSame($str1, $str2);
    }
}
