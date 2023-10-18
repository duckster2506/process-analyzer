<?php

namespace Duckster\Analyzer\Tests\Structures;

use PHPUnit\Framework\TestCase;
use Duckster\Analyzer\Structures\AnalysisRecord;

class AnalysisRecordTest extends TestCase
{
    public function testCanBeConstructedAndGotten(): void
    {
        // Create instance
        $obj = AnalysisRecord::open();

        // Is instance of AnalysisRecord
        $this->assertInstanceOf(AnalysisRecord::class, $obj);
        // Is data type correct
        $this->assertIsFloat($obj->getStartTime());
        $this->assertIsFloat($obj->getEndTime());
        $this->assertIsInt($obj->getRealMem());
        $this->assertIsInt($obj->getStartEmMem());
        $this->assertIsInt($obj->getEndEmMem());
        $this->assertIsInt($obj->getEmPeak());
        $this->assertIsInt($obj->getRealPeak());
        $this->assertIsInt($obj->getUsage());
    }

    public function testCanBeClosed(): void
    {
        // Memory before create an AnalysisRecord
        $start = memory_get_usage();
        // Create instance and start recording
        $obj = AnalysisRecord::open()->start();
        // Memory after create an AnalysisRecord
        $end = memory_get_usage();

        // Calculate the allocated memory for $obj
        $mem = $end - $start;

        // Check if Record's $startTime and $endTime is equal since it was created recently (may vary by a few ms)
        $this->assertSame(floor($obj->getStartTime()), floor($obj->getEndTime()));
        // Check if Record's $startEmMem and $endEmMem is equal since it was created recently
        $this->assertSame($obj->getStartEmMem(), $obj->getEndEmMem());

        // Sleep for 1s
        sleep(1);
        // Close
        $obj->close();

        // Check if Record's $startTime and $endTime is not equal
        $this->assertNotSame(floor($obj->getStartTime()), floor($obj->getEndTime()));
        // AnalysisRecord will save it own used memory
        $this->assertSame($mem, $obj->getUsage());
    }

    public function testCanFetchEndEmMemProperly(): void
    {
        // Memory before create an AnalysisRecord
        $start = memory_get_usage();
        // Create instance
        $obj = AnalysisRecord::open()->start();
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
        $obj = AnalysisRecord::open()->start();
        // Sleep for 3 second
        sleep(3);
        // Close Record
        $obj->close();

        $end = hrtime(true) / 1e+6;
        // floor() will only floor the interval to ms
        // It means that may vary by few 0.xx ms because of other operation
        $this->assertSame(floor($end - $start), floor($obj->diffTime()));
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
        $obj = AnalysisRecord::open()->start();
        // Create String
        $str2 = str_repeat(" ", 1024);
        // Close Record
        $obj->close();

        // Check Record emMem diff
        $this->assertSame($end - $start, $obj->diffEmMem());
        // Just to make sure $str1 equals $str2 and both will be keep
        $this->assertSame($str1, $str2);
    }
}
