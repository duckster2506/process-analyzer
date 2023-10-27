<?php

namespace Duckster\Analyzer\Tests\Structures;

use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\Structures\RecordRelation;
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
        $this->assertEmpty(0.0, $obj->getStopTime());
        $this->assertEquals(0, $obj->getStartMem());
        $this->assertEquals(0, $obj->getStopMem());
        $this->assertFalse($obj->isShared());
    }

    public function testCanAddRelations(): void
    {
        // Open a Record
        $record = AnalysisRecord::open("Relational record");
        // Create a child
        $child = AnalysisRecord::open("Child");

        // Check if both don't have any relation
        $this->assertEmpty($record->getRelations());
        $this->assertEmpty($child->getRelations());

        // Establish relation
        $record->establishRelation($child);

        // Check if relation list is not empty
        $this->assertCount(1, $record->getRelations());
        $this->assertCount(1, $child->getRelations());

        // Get the relation instance
        $relation = $record->getRelations()[0];

        // Check if $relation is instance of RecordRelation
        $this->assertInstanceOf(RecordRelation::class, $relation);
        // Check if both Record point to the same $relation
        $this->assertSame($relation, $child->getRelations()[0]);
        // Check if $record is the owner of relation
        $this->assertSame($record, $relation->getOwner());
        // Check if $child is the target of relation
        $this->assertSame($child, $relation->getTarget());
    }

    public function testCanStart(): void
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
        $afterStart = $obj->start();

        // Check isStarted flag
        $this->assertTrue($obj->isStarted());
        // Check if startTime != 0
        $this->assertNotEquals(0.0, $obj->getStartTime());
        // Check if startMem != 0
        $this->assertNotEquals(0.0, $obj->getStartMem());
        // Return self after start
        $this->assertSame($obj, $afterStart);
    }

    public function testCanStartOnce(): void
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

    public function testCanStop(): void
    {
        // Create instance and start recording
        $obj = AnalysisRecord::open("Record")->start();

        // Check if endTime = 0
        $this->assertEquals(0.0, $obj->getStopTime());
        // Check if endMem = 0
        $this->assertEquals(0.0, $obj->getStopMem());

        // Sleep for 1s
        sleep(1);
        // Stop
        $afterStop = $obj->stop();

        // Check if Record's $startTime and $endTime is not equal
        $this->assertNotEquals(0.0, $obj->getStopTime());
        // Check if Record's isStopped is true
        $this->assertTrue($obj->isStopped());
        // Check if stop return self
        $this->assertSame($obj, $afterStop);
    }

    public function testCanStopOnce(): void
    {
        // Create instance and start recording
        $obj = AnalysisRecord::open("Record")->start();
        // Stop
        $afterStop = $obj->stop();

        // Check isStopped flag
        $this->assertTrue($obj->isStopped());
        // Check if return self
        $this->assertSame($obj, $afterStop);
        // Get end timestamp
        $end = $obj->getStopTime();

        // Sleep for 1 second
        sleep(1);
        // Stop one more time
        $obj->stop();
        // Check if end timestamp is still intact
        $this->assertSame($end, $obj->getStopTime());
    }

    public function testCanHandleOwnershipRelationAfterStop(): void
    {
        // Create owner
        $owner = AnalysisRecord::open("Owner");
        // Create target
        $target = AnalysisRecord::open("Target");

        // Establish relation
        $owner->establishRelation($target);
        // Start $owner first
        $owner->start();
        // Start and stop $target second
        $target->start()->stop();
        // Then stop $owner
        $owner->stop();

        // Check if the $owner and $target has an ownership relation
        $this->assertFalse($owner->getRelations()[0]->isIntersect());
    }

    public function testCanHandleIntersectRelationAfterStop(): void
    {
        // Create owner
        $owner = AnalysisRecord::open("Owner");
        // Create target
        $target = AnalysisRecord::open("Target");

        // Establish relation
        $owner->establishRelation($target);
        // Start $owner first
        $owner->start();
        // Then start $target
        $target->start();
        // Then stop $owner
        $owner->stop();
        // Then stop $target
        $target->stop();

        // Check if the $owner and $target has an intersect relation
        $this->assertTrue($owner->getRelations()[0]->isIntersect());
    }

    public function testCanBranchWithoutRelation(): void
    {
        // Create instance and start recording
        $original = AnalysisRecord::open("Original")->start();
        $original->setPreStartSnapshot(Analyzer::takeSnapshot());
        $original->setPreStopSnapshot(Analyzer::takeSnapshot());

        // Branch
        $branch = $original->branch();

        // Not the same
        $this->assertNotSame($original, $branch);
        // UID and relation will be renewed
        $this->assertNotEquals($original->getUID(), $branch->getUID());
        // Everything else will be exactly the same
        $this->assertSame($original->getName(), $branch->getName());
        $this->assertSame($original->getStartTime(), $branch->getStartTime());
        $this->assertSame($original->getStopTime(), $branch->getStopTime());
        $this->assertSame($original->getStartMem(), $branch->getStartMem());
        $this->assertSame($original->getStopMem(), $branch->getStopMem());
        $this->assertSame($original->isStarted(), $branch->isStarted());
        $this->assertSame($original->isStopped(), $branch->isStopped());
        $this->assertSame($original->isShared(), $branch->isShared());
        $this->assertSame($original->getPreStartSnapshot(), $branch->getPreStartSnapshot());
        $this->assertSame($original->getPreStopSnapshot(), $branch->getPreStopSnapshot());
    }

    public function testCanBranchWithRelation(): void
    {
        // Create instance and start recording
        $original = AnalysisRecord::open("Original")->start();
        $original->setPreStartSnapshot(Analyzer::takeSnapshot());
        $original->setPreStopSnapshot(Analyzer::takeSnapshot());

        // Ownership relation
        $ownership = AnalysisRecord::open("To be owned")->start();
        // Intersect relation
        $intersect = AnalysisRecord::open("To be intersected")->start();

        // Add relation
        $original->establishRelation($ownership);
        $original->establishRelation($intersect);

        // Stop
        $ownership->stop();
        $original->stop();
        $intersect->stop();

        // Branch
        $branch = $original->branch();

        // Not the same
        $this->assertNotSame($original, $branch);
        // UID and relation will be renewed
        $this->assertNotEquals($original->getUID(), $branch->getUID());
        $this->assertNotSame($original->getRelations(), $branch->getRelations());
        // But it's size is equal
        $this->assertEquals(count($original->getRelations()), count($branch->getRelations()));
        // Everything else will be exactly the same
        $this->assertSame($original->getName(), $branch->getName());
        $this->assertSame($original->getStartTime(), $branch->getStartTime());
        $this->assertSame($original->getStopTime(), $branch->getStopTime());
        $this->assertSame($original->getStartMem(), $branch->getStartMem());
        $this->assertSame($original->getStopMem(), $branch->getStopMem());
        $this->assertSame($original->isStarted(), $branch->isStarted());
        $this->assertSame($original->isStopped(), $branch->isStopped());
        $this->assertSame($original->isShared(), $branch->isShared());
        $this->assertSame($original->getPreStartSnapshot(), $branch->getPreStartSnapshot());
        $this->assertSame($original->getPreStopSnapshot(), $branch->getPreStopSnapshot());
        // Iterate through each relation to perform assertion
        for ($i = 0; $i < 2; $i++) {
            // The RecordRelation object will be renewed
            $this->assertNotSame($original->getRelations()[$i], $branch->getRelations()[$i]);
            // Relation type is equal
            $this->assertEquals($original->getRelations()[$i]->isIntersect(), $branch->getRelations()[$i]->isIntersect());
            // Check owner or target
            if ($original->getRelations()[$i]->getOwner() === $original) {
                // If $original is an owner in old relation, so will $branch
                $this->assertSame($branch, $branch->getRelations()[$i]->getOwner());
            } else {
                // Else, so will $branch
                $this->assertSame($branch, $branch->getRelations()[$i]->getTarget());
            }
            $this->assertSame($branch, $branch->getRelations()[$i]->getOwner());
            // Target is the same
            $this->assertSame($original->getRelations()[$i]->getTarget(), $branch->getRelations()[$i]->getTarget());
        }
    }

    public function testCanCalculatePrepTime(): void
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

        // Take snapshot
        $endSS = Analyzer::takeSnapshot();
        // Sleep (this is the post end stage, do whatever we want)
        sleep(1);
        // Stop
        $record->stop();

        // Set snapshot
        $record->setPreStartSnapshot($startSS)->setPreStopSnapshot($endSS);

        // Get timeDiff
        $timeDiff = $end - $start;

        // Operation 1: Sleep 2s; Operation 2: Sleep 4s (2s for preparation)
        // We can not expect 2 operation will generate the same time diff since the time to create timestamp might be different each time
        // And there may be still have redundant time for machine to actually put to sleep and wakeup
        // The time diff of 2 operation may vary between 0 and 20 (usually between 1~5 and sometime bigger)
        // And the time diff of 2 operation may sometime bigger or smaller compare to each other
        $this->assertLessThanOrEqual(20, abs($timeDiff - $record->prepTime()) / 1e+6);
        // To make sure $timeDiff is more than 2s
        $this->assertGreaterThanOrEqual(2000, $timeDiff);
        // To make sure $record->diffTime is more than 2s
        $this->assertGreaterThanOrEqual(2000, $record->prepTime());
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
        // Stop Record
        $obj->stop();

        // Get timeDiff
        $timeDiff = $end - $start;

        // timeDiff = 0 if not stopped
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

    public function testReturnZeroTimeDiffIfNotStopped(): void
    {
        // Open Record
        $obj = AnalysisRecord::open("Record")->start();
        // Create String
        sleep(1);
        // memDiff = 0 if not stopped
        $this->assertEquals(0.0, $obj->diffTime());
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
        // Stop
        $record->stop();


        // Set snapshot
        $record->setPreStartSnapshot($startSS);

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

    public function testCanExcludePreStopPreparationTimeOutOfTimeDiff(): void
    {
        // Get timestamp
        $start = hrtime(true);
        // Sleep 2s (main logic)
        sleep(2);
        // Get timestamp
        $end = hrtime(true);

        // Create Record
        $record = AnalysisRecord::open("New record");
        // Start
        $record->start();
        // Sleep 2s (main logic)
        sleep(2);
        // Take snapshot
        $endSS = Analyzer::takeSnapshot();
        // Sleep (this is the post end stage, do whatever we want)
        sleep(2);
        // Stop
        $record->stop();

        // Set snapshot
        $record->setPreStopSnapshot($endSS);

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
        // Take snapshot
        $endSS = Analyzer::takeSnapshot();
        // Sleep (this is the post end stage, do whatever we want)
        sleep(1);
        // Stop
        $record->stop();

        // Set snapshot
        $record->setPreStartSnapshot($startSS)
            ->setPreStopSnapshot($endSS);

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

    public function testCanCalculatePrepEmMem(): void
    {
        // Warmup
        Analyzer::takeSnapshot();
        // Get timestamp
        $start = memory_get_usage();
        // Create String (main logic)
        $str1 = str_repeat(" ", 2048);
        // Since there are 5 snapshot is created to set pre start and start, we must replicate it
        $rep1 = Analyzer::takeSnapshot();
        $rep2 = Analyzer::takeSnapshot();
        $rep3 = Analyzer::takeSnapshot();
        $rep4 = Analyzer::takeSnapshot();
        $rep5 = Analyzer::takeSnapshot();
        // Get timestamp
        $end = memory_get_usage();

        // Create Record
        $record = AnalysisRecord::open("New record");
        // Take snapshot
        $startSS = Analyzer::takeSnapshot();
        // Create String (this is the pre start stage, do whatever we want)
        $str2 = str_repeat(" ", 1024);
        // Start
        $record->start();

        // Take snapshot
        $endSS = Analyzer::takeSnapshot();
        // Create String (this is the pre start stage, do whatever we want)
        $str3 = str_repeat(" ", 1024);
        // Stop
        $record->stop();

        // Set snapshot
        $record->setPreStartSnapshot($startSS)
            ->setPreStopSnapshot($endSS);

        // Get timeDiff
        $memDiff = $end - $start;

        // Check if Record's memDiff excludes it mem usage
        $this->assertSame($memDiff, $record->prepMem());
        // These operation is to make sure $str[x] will be kept out of GC
        $this->assertIsString($str1);
        $this->assertIsString($str2);
        $this->assertIsString($str3);
        $this->assertIsArray($rep1);
        $this->assertIsArray($rep2);
        $this->assertIsArray($rep3);
        $this->assertIsArray($rep4);
        $this->assertIsArray($rep5);
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
        // Stop Record
        $obj->stop();

        // Check Record emMem diff
        $this->assertSame($end - $start, $obj->diffMem());
        // Just to make sure $str1 equals $str2 and both will be kept out of GC
        $this->assertSame($str1, $str2);
    }

    public function testReturnZeroMemDiffIfNotStopped(): void
    {
        // Open Record
        $obj = AnalysisRecord::open("Record")->start();
        // Create String
        $str = str_repeat(" ", 1024);
        // memDiff = 0 if not stopped
        $this->assertEquals(0, $obj->diffMem());
        // Stop Record
        $obj->stop();

        $this->assertIsString($str);
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
        // Stop
        $record->stop();

        // Set snapshot
        $record->setPreStartSnapshot($startSS);

        // Get timeDiff
        $memDiff = $end - $start;

        // Check if Record's memDiff excludes it mem usage
        $this->assertSame($memDiff, $record->diffMem());
        // These operation is to make sure $str[x] will be kept out of GC
        $this->assertIsString($str1);
        $this->assertIsString($str2);
        $this->assertIsString($str3);
    }

    public function testCanExcludePreStopPreparationMemOutOfMemDiff(): void
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
        // Take snapshot
        $endSS = Analyzer::takeSnapshot();
        // Create String (this is the pre start stage, do whatever we want)
        $str3 = str_repeat(" ", 2048);
        // Stop
        $record->stop();

        // Set snapshot
        $record->setPreStartSnapshot($startSS)
            ->setPreStopSnapshot($endSS);

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
        // Take snapshot
        $endSS = Analyzer::takeSnapshot();
        // Create String (this is the pre start stage, do whatever we want)
        $str4 = str_repeat(" ", 1024);
        // Stop
        $record->stop();

        // Set snapshot
        $record->setPreStartSnapshot($startSS)
            ->setPreStopSnapshot($endSS);

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
            // Take snapshot
            $endSS = Analyzer::takeSnapshot();
            // Sleep (this is the post end stage, do whatever we want)
            usleep(1000);
            // Stop
            $record->stop();

            // Set snapshot
            $record->setPreStartSnapshot($startSS)
                ->setPreStopSnapshot($endSS);

            // Increase total error (in μs (microsecond))
            $total += abs(($end - $start) - $record->diffTime()) / 1e+3;
        }

        // This test will show the error between 2 operation compare to each other
        // The average error between diffs will vary between 0 - 20 μs
        $this->assertLessThanOrEqual(20, $total / 1000);
    }
}
