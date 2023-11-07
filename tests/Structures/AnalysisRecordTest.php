<?php

namespace Duckstery\Analyzer\Tests\Structures;

use Duckstery\Analyzer\Analyzer;
use Duckstery\Analyzer\Structures\RecordRelation;
use Duckstery\Analyzer\Utils;
use PHPUnit\Framework\TestCase;
use Duckstery\Analyzer\Structures\AnalysisRecord;

class AnalysisRecordTest extends TestCase
{
    private static int $count = 5;

    public function setUp(): void
    {
        parent::setUp();
        self::$count = 5;
    }

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
        $this->assertIsArray($obj->getPreStartSnapshot());
        $this->assertIsArray($obj->getPreStopSnapshot());
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

    public function testCanAddExtrasAtStart(): void
    {
        // Get extras
        $extras = [
            "count" => [
                "handler" => [$this, "increaseCount"],
                "start" => true,
            ]
        ];

        // Create owner
        $record = AnalysisRecord::open("Record");
        // Start and stop
        $record->start($extras)->stop($extras);

        // This extras will be gotten at start
        $this->assertArrayHasKey("start count", $record->getExtras());
        // Check value
        $this->assertEquals(5, $record->getExtras()["start count"]);
        // This extras won't be gotten at stop
        $this->assertArrayNotHasKey("stop count", $record->getExtras());
    }

    public function testCanAddExtrasAtStop(): void
    {
        // Get extras
        $extras = [
            "count" => [
                "handler" => [$this, "increaseCount"],
                "stop" => true,
            ]
        ];

        // Create owner
        $record = AnalysisRecord::open("Record");
        // Start and stop
        $record->start($extras)->stop($extras);

        // This extras won't be gotten at start
        $this->assertArrayNotHasKey("start count", $record->getExtras());
        // This extras will be gotten at stop
        $this->assertArrayHasKey("stop count", $record->getExtras());
        // Check value
        $this->assertEquals(5, $record->getExtras()["stop count"]);
    }

    public function testCanAddExtrasWithDiff(): void
    {
        // Get extras
        $extras = [
            "count" => [
                "handler" => [$this, "increaseCount"],
                "start" => true,
                "stop" => true,
                "diff" => true
            ]
        ];

        // Create owner
        $record = AnalysisRecord::open("Record");
        // Start and stop
        $record->start($extras)->stop($extras);

        // This extras will be gotten at start
        $this->assertArrayHasKey("start count", $record->getExtras());
        // Check value
        $this->assertEquals(5, $record->getExtras()["start count"]);
        // This extras will be gotten at stop
        $this->assertArrayHasKey("stop count", $record->getExtras());
        // Check value
        $this->assertEquals(6, $record->getExtras()["stop count"]);
        // Extras diff between start and stop will be included
        $this->assertArrayHasKey("diff count", $record->getExtras());
        // Check value
        $this->assertEquals(1, $record->getExtras()["diff count"]);
    }

    public function testCanAddExtrasWithNoDiffIfStartOrStopIsFalse(): void
    {
        // Get extras
        $extras = [
            "count" => [
                "handler" => [$this, "increaseCount"],
                "start" => false,
                "stop" => true,
                "diff" => true
            ]
        ];

        // Create owner
        $record = AnalysisRecord::open("Record");
        // Start and stop
        $record->start($extras)->stop($extras);

        // No diff if "start" or "stop" is false
        $this->assertArrayNotHasKey("diff count", $record->getExtras());

        // Get extras
        $extras = [
            "count" => [
                "handler" => [$this, "increaseCount"],
                "start" => true,
                "stop" => false,
                "diff" => true
            ]
        ];

        // Create owner
        $record = AnalysisRecord::open("Record");
        // Start and stop
        $record->start($extras)->stop($extras);

        // No diff if "start" or "stop" is false
        $this->assertArrayNotHasKey("diff count", $record->getExtras());
    }

    public function testCanAddExtrasWithFormatter(): void
    {
        // Get extras
        $extras = [
            "count" => [
                "handler" => [$this, "increaseCount"],
                "formatter" => [$this, "addPrefix"],
                "start" => true,
                "stop" => true,
                "diff" => true
            ]
        ];

        // Create owner
        $record = AnalysisRecord::open("Record");
        // Start and stop
        $record->start($extras)->stop($extras);

        // This extras will be gotten at start
        $this->assertArrayHasKey("start count", $record->getExtras());
        // Check value
        $this->assertEquals("Count: 5", $record->getExtras()["start count"]);
        // This extras will be gotten at stop
        $this->assertArrayHasKey("stop count", $record->getExtras());
        // Check value
        $this->assertEquals("Count: 6", $record->getExtras()["stop count"]);
        // Extras diff between start and stop will be included
        $this->assertArrayHasKey("diff count", $record->getExtras());
        // Check value
        $this->assertEquals("Count: 1", $record->getExtras()["diff count"]);
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
        // The time diff of 2 operation may vary between 0 and 100 μs (usually between 1~5 and sometime bigger)
        // And the time diff of 2 operation may sometime bigger or smaller compare to each other
        $this->assertLessThanOrEqual(100, abs($timeDiff - $record->prepTime()) / 1e+6);
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
        // The time diff of 2 operation may vary between 100 μs (usually between 1~5 and sometime bigger)
        // And the time diff of 2 operation may sometime bigger or smaller compare to each other
        $this->assertLessThanOrEqual(100, abs($timeDiff - $obj->diffTime()) / 1e+6);
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
        // The time diff of 2 operation may vary between 100 μs (usually between 1~5 and sometime bigger)
        // And the time diff of 2 operation may sometime bigger or smaller compare to each other
        $this->assertLessThanOrEqual(100, abs($timeDiff - $record->diffTime()) / 1e+6);
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
        // The time diff of 2 operation may vary between 100 μs (usually between 1~5 and sometime bigger)
        // And the time diff of 2 operation may sometime bigger or smaller compare to each other
        $this->assertLessThanOrEqual(100, abs($timeDiff - $record->diffTime()) / 1e+6);
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
        // The time diff of 2 operation may vary between 0 and 100 μs (usually between 1~5 and sometime bigger)
        // And the time diff of 2 operation may sometime bigger or smaller compare to each other
        $this->assertLessThanOrEqual(100, abs($timeDiff - $record->diffTime()) / 1e+6);
        // To make sure $timeDiff is more than 2s
        $this->assertGreaterThanOrEqual(2000, $timeDiff);
        // To make sure $record->diffTime is more than 2s
        $this->assertGreaterThanOrEqual(2000, $record->diffTime());
    }

    public function testCanCalculateActualTimeThatExcludeSelfAndOwnershipRelationPrepTime(): void
    {
        // Create owner and start
        $owner = AnalysisRecord::open("Owner")
            ->setPreStartSnapshot(Analyzer::takeSnapshot())
            ->start();

        // This snapshot will mark the preparation time of target before start
        $target = AnalysisRecord::open("Target")->setPreStartSnapshot(Analyzer::takeSnapshot());
        // Add relation
        $owner->establishRelation($target);
        // Sleep
        sleep(1);
        // Start
        $target->start();

        // This snapshot will mark the preparation time of target before stop
        $target->setPreStopSnapshot(Analyzer::takeSnapshot());
        // Sleep
        sleep(1);
        // Stop
        $target->stop();

        // Stop owner
        $owner
            ->setPreStopSnapshot(Analyzer::takeSnapshot())
            ->stop();

        // Actual execution time of $owner will lower than 2s
        $this->assertLessThan(2, $owner->actualTime() / 1e+9);
        // While diff time is higher than 2s
        $this->assertGreaterThanOrEqual(2, $owner->diffTime() / 1e+9);
        // Actual execution time of $owner will be diffTime and exclude all relation's prep time
        $this->assertEquals($owner->diffTime() - $target->preStartPrepTime() - $target->preStopPrepTime(), $owner->actualTime());
    }

    public function testCanCalculateActualTimeThatExcludeSelfAndIntersectRelationPrepTime(): void
    {
        // Create owner and start
        $owner = AnalysisRecord::open("Owner")
            ->setPreStartSnapshot(Analyzer::takeSnapshot())
            ->start();

        // This snapshot will mark the preparation time of target before start
        $target = AnalysisRecord::open("Target")->setPreStartSnapshot(Analyzer::takeSnapshot());

        // Add relation
        $owner->establishRelation($target);
        // Sleep
        sleep(1);
        // Start
        $target->start();

        // This snapshot will mark the preparation time of owner before stop
        $owner->setPreStopSnapshot(Analyzer::takeSnapshot());
        // Sleep
        sleep(1);
        // Stop owner
        $owner->stop();

        // Stop target
        $target->setPreStopSnapshot(Analyzer::takeSnapshot())->stop();

        // Actual execution time of $owner will lower than 1s
        $this->assertLessThan(1, $owner->actualTime() / 1e+9);
        // While diff time is higher than 1s
        $this->assertGreaterThanOrEqual(1, $owner->diffTime() / 1e+9);
        // Actual execution time of $target will lower than 1s
        $this->assertLessThan(1, $target->actualTime() / 1e+9);
        // While diff time is higher than 1s
        $this->assertGreaterThanOrEqual(1, $target->diffTime() / 1e+9);
        // Actual execution time of $owner will be diffTime and exclude $target preStart prep time
        $this->assertEquals($owner->diffTime() - $target->preStartPrepTime(), $owner->actualTime());
        // Actual execution time of $target will be diffTime and exclude $owner preStop prep time
        $this->assertEquals($target->diffTime() - $owner->preStopPrepTime(), $target->actualTime());
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
    }

    public function testCanCalculateActualTimeThatExcludeSelfAndOwnershipRelationPrepMem(): void
    {
        // Create owner and start
        $owner = AnalysisRecord::open("Owner")
            ->setPreStartSnapshot(Analyzer::takeSnapshot())
            ->start();

        // This snapshot will mark the preparation time of target before start
        $snapshot = Analyzer::takeSnapshot();
        $target = AnalysisRecord::open("Target")->setPreStartSnapshot($snapshot);
        // Add relation
        $owner->establishRelation($target);
        // Sleep
        $str1 = str_repeat(" ", 1024);
        // Start
        $target->start();

        // This snapshot will mark the preparation time of target before stop
        $target->setPreStopSnapshot(Analyzer::takeSnapshot());
        // Sleep
        $str2 = str_repeat(" ", 1024);
        // Stop
        $target->stop();

        // Stop owner
        $owner
            ->setPreStopSnapshot(Analyzer::takeSnapshot())
            ->stop();

        // Actual execution mem of $owner is 0
        $this->assertEquals(0, $owner->actualMem());
        // While diff mem is higher than 0
        $this->assertGreaterThan(0, $owner->diffMem());
        // Actual execution mem of $owner will be diffMem and exclude $target prep mem
        $this->assertEquals($owner->diffMem() - $target->preStartPrepMem() - $target->preStopPrepMem(), $owner->actualMem());
    }

    public function testCanCalculateActualTimeThatExcludeSelfAndIntersectRelationPrepMem(): void
    {
        // Create owner and start
        $owner = AnalysisRecord::open("Owner")
            ->setPreStartSnapshot(Analyzer::takeSnapshot())
            ->start();

        // This snapshot will mark the preparation time of target before start
        $snapshot = Analyzer::takeSnapshot();
        $target = AnalysisRecord::open("Target")->setPreStartSnapshot($snapshot);

        // Add relation
        $owner->establishRelation($target);
        // Sleep
        $str1 = str_repeat(" ", 1024);
        // Start
        $target->start();

        // This snapshot will mark the preparation time of owner before stop
        $owner->setPreStopSnapshot(Analyzer::takeSnapshot());
        // Sleep
        $str2 = str_repeat(" ", 1024);
        // Stop owner
        $owner->stop();

        // Stop target
        $target->setPreStopSnapshot(Analyzer::takeSnapshot())->stop();

        // Actual execution time of $owner is 0
        $this->assertEquals(0, $owner->actualMem());
        // While diff time is higher than 0
        $this->assertGreaterThan(0, $owner->diffMem());
        // Actual execution time of $target is 0
        $this->assertEquals(0, $target->actualMem());
        // While diff time is higher than 0
        $this->assertGreaterThan(0, $target->diffMem());
        // Actual execution time of $owner will be diffTime and exclude $target preStart prep time
        $this->assertEquals($owner->diffMem() - $target->preStartPrepMem(), $owner->actualMem());
        // Actual execution time of $target will be diffTime and exclude $owner preStop prep time
        $this->assertEquals($target->diffMem() - $owner->preStopPrepMem(), $target->actualMem());
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
        // The average error between diffs will vary between 0 - 100 μs
        $this->assertLessThanOrEqual(100, $total / 1000);
    }

    public function increaseCount(): int
    {
        return self::$count++;
    }

    public function addPrefix(int $value): string
    {
        return "Count: $value";
    }
}
