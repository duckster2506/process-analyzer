<?php

namespace Duckster\Analyzer\Tests\Structures;

use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\Structures\RecordRelation;
use PHPUnit\Framework\TestCase;
use Duckster\Analyzer\Structures\AnalysisRecord;

class RecordRelationTest extends TestCase
{
    public function testCanBeConstructedAndGotten(): void
    {
        // Create owner
        $owner = AnalysisRecord::open("Owner");
        // Create target
        $target = AnalysisRecord::open("Target");
        // Create instance
        $relation = new RecordRelation($owner, $target);

        // Is data type correct
        $this->assertSame($owner, $relation->getOwner());
        $this->assertSame($target, $relation->getTarget());
        // This default value is False
        $this->assertFalse($relation->isIntersect());
    }

    public function testCanSetRelationTypeToIntersect(): void
    {
        // Create owner
        $owner = AnalysisRecord::open("Owner");
        // Create target
        $target = AnalysisRecord::open("Target");
        // Create instance
        $relation = new RecordRelation($owner, $target);
        // Set
        $relation->intersect();

        // Check flag
        $this->assertTrue($relation->isIntersect());
    }
}
