<?php

namespace Duckstery\Analyzer\Tests\Structures;

use Duckstery\Analyzer\Structures\AnalysisDataset;
use PHPUnit\Framework\TestCase;

class AnalysisDatasetTest extends TestCase
{
    public function testCanBeConstructedAndGotten(): void
    {
        // Create config
        $obj = new AnalysisDataset(10);

        // Check type
        $this->assertInstanceOf(AnalysisDataset::class, $obj);
        $this->assertEquals(10, $obj->getMaxLength());
    }

    public function testCanAdd(): void
    {
        // Create config
        $obj = new AnalysisDataset(10);
        // Create a string with 9 character
        $data = str_repeat("a", 9);
        // Add to dataset
        $obj->add($data);

        // Check value
        $this->assertEquals($data, $obj->get(0));
        // Check maxLength, since 9 < 10, then maxLength is 10
        $this->assertEquals(10, $obj->getMaxLength());

        // Create a string with 11 character to dataset
        $data1 = str_repeat("b", 11);
        // Add to dataset
        $obj->add($data1);

        // Check value
        $this->assertEquals($data1, $obj->get(1));
        // Check maxLength, since 10 < 11, then maxLength is 11
        $this->assertEquals(11, $obj->getMaxLength());
    }
}
