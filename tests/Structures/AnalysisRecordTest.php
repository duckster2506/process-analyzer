<?php

namespace Duckster\Analyzer\Tests\Structures;

use Duckster\Analyzer\AnalysisUtils;
use Duckster\Analyzer\Analyzer;
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
        $this->assertEmpty(0.0, $obj->getStartTime());
        $this->assertEmpty(0.0, $obj->getEndTime());
        $this->assertEquals(0, $obj->getStartMem());
        $this->assertEquals(0, $obj->getEndMem());
        $this->assertFalse($obj->isShared());
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
        // Check if startTime = 0
        $this->assertEquals(0.0, $obj->getStartTime());
        // Check if startMem = 0
        $this->assertEquals(0.0, $obj->getStartMem());

        // Start
        $obj->start();
        // Get $start timestamp
        $start = $obj->getStartTime();

        // Check isStarted flag
        $this->assertTrue($obj->isStarted());
        // Check if startTime != 0
        $this->assertNotEquals(0.0, $obj->getStartTime());
        // Check if startMem != 0
        $this->assertNotEquals(0.0, $obj->getStartMem());

        // Sleep
        sleep(1);
        // Start
        $obj->start();

        // $start timestamp didn't change after the second start()
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

        // Check if endTime = 0
        $this->assertEquals(0.0, $obj->getEndTime());
        // Check if endMem = 0
        $this->assertEquals(0.0, $obj->getEndMem());

        // Sleep for 1s
        sleep(1);
        // Close
        $obj->close();

        // Check if Record's $startTime and $endTime is not equal
        $this->assertNotEquals(0.0, $obj->getEndTime());
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

    public function testCanCalculateTimeDiff(): void
    {
        // Get the start of execution
        $start = hrtime(true);
        // Sleep for 2 second
        sleep(2);
        // Get end of execution
        $end = hrtime(true);

        // Create instance
        $obj = AnalysisRecord::open("Record")->start();
        // Sleep for 2 second
        sleep(2);
        // Close Record
        $obj->close();

        // Get timeDiff
        $timeDiff = $end - $start;

        // timeDiff = 0 if not closed
        $this->assertEquals(0.0, AnalysisRecord::open("Nonstop record")->start()->diffTime());

        // Operation 1: Sleep 2s; Operation 2: Sleep 4s (2s for preparation)
        // We can not expect 2 operation will generate the same time diff since the time to create timestamp might be different each time
        // And there may be still have redundant time for machine to actually put to sleep and wakeup
        // The time diff of 2 operation may vary between 20 (usually between 1~5 and sometime bigger)
        // And the time diff of 2 operation may sometime bigger or smaller compare to each other
        $this->assertLessThanOrEqual(20, abs($timeDiff - $obj->diffTime()) / 1e+6);
        // To make sure $timeDiff is more than 2s
        $this->assertGreaterThanOrEqual(2000, $timeDiff);
        // To make sure $obj->diffTime is more than 2s
        $this->assertGreaterThanOrEqual(2000, $obj->diffTime());
    }

    public function testCanExcludePreStartPreparationTimeOutOfTimeDiff(): void
    {
        // Get timestamp
        $start = hrtime(true);
        // Sleep 2s (main logic)
        sleep(2);
        // Get timestamp
        $end = hrtime(true);

        // Take snapshot
        $startSS = Analyzer::takeSnapshot();
        // Create Record
        $record = AnalysisRecord::open("New record");
        // Sleep (this is the pre start stage, do whatever we want)
        sleep(2);
        // Start
        $record->start();
        // Sleep 2s (main logic)
        sleep(2);
        // Close
        $record->close();
        // Take snapshot
        $endSS = Analyzer::takeSnapshot(false);

        // Set snapshot
        $record->setPreSnapshot($startSS)
            ->setPostSnapshot($endSS);

        // Get timeDiff
        $timeDiff = $end - $start;

        // Operation 1: Sleep 2s; Operation 2: Sleep 4s (2s for preparation)
        // We can not expect 2 operation will generate the same time diff since the time to create timestamp might be different each time
        // And there may be still have redundant time for machine to actually put to sleep and wakeup
        // The time diff of 2 operation may vary between 20 (usually between 1~5 and sometime bigger)
        // And the time diff of 2 operation may sometime bigger or smaller compare to each other
        $this->assertLessThanOrEqual(20, abs($timeDiff - $record->diffTime()) / 1e+6);
        // To make sure $timeDiff is more than 2s
        $this->assertGreaterThanOrEqual(2000, $timeDiff);
        // To make sure $record->diffTime is more than 2s
        $this->assertGreaterThanOrEqual(2000, $record->diffTime());
    }

    public function testCanExcludePostEndPreparationTimeOutOfTimeDiff(): void
    {
        // Get timestamp
        $start = hrtime(true);
        // Sleep 2s (main logic)
        sleep(2);
        // Get timestamp
        $end = hrtime(true);

        // Take snapshot
        $startSS = Analyzer::takeSnapshot();
        // Create Record
        $record = AnalysisRecord::open("New record");
        // Start
        $record->start();
        // Sleep 2s (main logic)
        sleep(2);
        // Close
        $record->close();
        // Sleep (this is the post end stage, do whatever we want)
        sleep(2);
        // Take snapshot
        $endSS = Analyzer::takeSnapshot(false);

        // Set snapshot
        $record->setPreSnapshot($startSS)
            ->setPostSnapshot($endSS);

        // Get timeDiff
        $timeDiff = $end - $start;

        // Operation 1: Sleep 2s; Operation 2: Sleep 4s (2s for preparation)
        // We can not expect 2 operation will generate the same time diff since the time to create timestamp might be different each time
        // And there may be still have redundant time for machine to actually put to sleep and wakeup
        // The time diff of 2 operation may vary between 20 (usually between 1~5 and sometime bigger)
        // And the time diff of 2 operation may sometime bigger or smaller compare to each other
        $this->assertLessThanOrEqual(20, abs($timeDiff - $record->diffTime()) / 1e+6);
        // To make sure $timeDiff is more than 2s
        $this->assertGreaterThanOrEqual(2000, $timeDiff);
        // To make sure $record->diffTime is more than 2s
        $this->assertGreaterThanOrEqual(2000, $record->diffTime());
    }

    public function testCanExcludePreparationTimeOutOfTimeDiff(): void
    {
        // Get timestamp
        $start = hrtime(true);
        // Sleep 2s (main logic)
        sleep(2);
        // Get timestamp
        $end = hrtime(true);

        // Take snapshot
        $startSS = Analyzer::takeSnapshot();
        // Create Record
        $record = AnalysisRecord::open("New record");
        // Sleep (this is the pre start stage, do whatever we want)
        sleep(1);
        // Start
        $record->start();
        // Sleep 2s (main logic)
        sleep(2);
        // Close
        $record->close();
        // Sleep (this is the post end stage, do whatever we want)
        sleep(1);
        // Take snapshot
        $endSS = Analyzer::takeSnapshot(false);

        // Set snapshot
        $record->setPreSnapshot($startSS)
            ->setPostSnapshot($endSS);

        // Get timeDiff
        $timeDiff = $end - $start;

        // Operation 1: Sleep 2s; Operation 2: Sleep 4s (2s for preparation)
        // We can not expect 2 operation will generate the same time diff since the time to create timestamp might be different each time
        // And there may be still have redundant time for machine to actually put to sleep and wakeup
        // The time diff of 2 operation may vary between 0 and 20 (usually between 1~5 and sometime bigger)
        // And the time diff of 2 operation may sometime bigger or smaller compare to each other
        $this->assertLessThanOrEqual(20, abs($timeDiff - $record->diffTime()) / 1e+6);
        // To make sure $timeDiff is more than 2s
        $this->assertGreaterThanOrEqual(2000, $timeDiff);
        // To make sure $record->diffTime is more than 2s
        $this->assertGreaterThanOrEqual(2000, $record->diffTime());
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
        // memDiff = 0 if not closed
        $this->assertEquals(0, $obj->diffMem());
        // Close Record
        $obj->close();

        // Check Record emMem diff
        $this->assertSame($end - $start, $obj->diffMem());
        // Just to make sure $str1 equals $str2 and both will be kept out of GC
        $this->assertSame($str1, $str2);
    }

    public function testCanExcludePreStartPreparationMemOutOfMemDiff(): void
    {
        // Get timestamp
        $start = memory_get_usage();
        // Create String (main logic)
        $str1 = str_repeat(" ", 1024);
        // Get timestamp
        $end = memory_get_usage();

        // Take snapshot
        $startSS = Analyzer::takeSnapshot();
        // Create Record
        $record = AnalysisRecord::open("New record");
        // Create String (this is the pre start stage, do whatever we want)
        $str2 = str_repeat(" ", 2048);
        // Start
        $record->start();
        // Create String (main logic)
        $str3 = str_repeat(" ", 1024);
        // Close
        $record->close();
        // Take snapshot
        $endSS = Analyzer::takeSnapshot(false);

        // Set snapshot
        $record->setPreSnapshot($startSS)
            ->setPostSnapshot($endSS);

        // Get timeDiff
        $memDiff = $end - $start;

        // Check if Record's memDiff excludes it mem usage
        $this->assertSame($memDiff, $record->diffMem());
        // These operation is to make sure $str[x] will be kept out of GC
        $this->assertIsString($str1);
        $this->assertIsString($str2);
        $this->assertIsString($str3);
    }

    public function testCanExcludePostEndPreparationMemOutOfMemDiff(): void
    {
        // Get timestamp
        $start = memory_get_usage();
        // Create String (main logic)
        $str1 = str_repeat(" ", 1024);
        // Get timestamp
        $end = memory_get_usage();

        // Take snapshot
        $startSS = Analyzer::takeSnapshot();
        // Create Record
        $record = AnalysisRecord::open("New record");
        // Start
        $record->start();
        // Create String (main logic)
        $str2 = str_repeat(" ", 1024);
        // Close
        $record->close();
        // Create String (this is the pre start stage, do whatever we want)
        $str3 = str_repeat(" ", 2048);
        // Take snapshot
        $endSS = Analyzer::takeSnapshot(false);

        // Set snapshot
        $record->setPreSnapshot($startSS)
            ->setPostSnapshot($endSS);

        // Get timeDiff
        $memDiff = $end - $start;

        // Check if Record's memDiff excludes it mem usage
        $this->assertSame($memDiff, $record->diffMem());
        // These operation is to make sure $str[x] will be kept out of GC
        $this->assertIsString($str1);
        $this->assertIsString($str2);
        $this->assertIsString($str3);
    }

    public function testCanExcludePreparationMemOutOfMemDiff(): void
    {
        // Get timestamp
        $start = memory_get_usage();
        // Create String (main logic)
        $str1 = str_repeat(" ", 1024);
        // Get timestamp
        $end = memory_get_usage();

        // Take snapshot
        $startSS = Analyzer::takeSnapshot();
        // Create Record
        $record = AnalysisRecord::open("New record");
        // Create String (this is the pre start stage, do whatever we want)
        $str2 = str_repeat(" ", 1024);
        // Start
        $record->start();
        // Create String (main logic)
        $str3 = str_repeat(" ", 1024);
        // Close
        $record->close();
        // Create String (this is the pre start stage, do whatever we want)
        $str4 = str_repeat(" ", 1024);
        // Take snapshot
        $endSS = Analyzer::takeSnapshot(false);

        // Set snapshot
        $record->setPreSnapshot($startSS)
            ->setPostSnapshot($endSS);

        // Get timeDiff
        $memDiff = $end - $start;

        // Check if Record's memDiff excludes it mem usage
        $this->assertSame($memDiff, $record->diffMem());
        // These operation is to make sure $str[x] will be kept out of GC
        $this->assertSame($str1, $str2);
        $this->assertSame($str2, $str3);
        $this->assertSame($str3, $str4);
    }

    public function testTimeDiffError(): void
    {
        // Total error
        $total = 0.0;

        for ($i = 0; $i < 1000; $i++) {
            // Get timestamp
            $start = hrtime(true);
            // Get timestamp
            $end = hrtime(true);

            // Take snapshot
            $startSS = Analyzer::takeSnapshot();
            // Create Record
            $record = AnalysisRecord::open("New record");
            // Sleep for 1ms (this is the pre start stage, do whatever we want)
            usleep(1000);
            // Start
            $record->start();
            // Close
            $record->close();
            // Sleep (this is the post end stage, do whatever we want)
            usleep(1000);
            // Take snapshot
            $endSS = Analyzer::takeSnapshot(false);

            // Set snapshot
            $record->setPreSnapshot($startSS)
                ->setPostSnapshot($endSS);

            // Increase total error (in μs (microsecond))
            $total += abs(($end - $start) - $record->diffTime()) / 1e+3;
        }

        // This test will show the error between 2 operation compare to each other
        // The average error between diffs will vary between 0 - 20 μs
        $this->assertLessThanOrEqual(20, $total / 1000);
    }
}
