<?php

namespace Duckster\Analyzer\Tests;

use Duckster\Analyzer\AnalysisUtils;
use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\Structures\AnalysisProfile;
use Duckster\Analyzer\Structures\AnalysisRecord;
use PHPUnit\Framework\TestCase;

class AnalyzerEntryTest extends TestCase
{
    public function testCanReturnRecordAfterClose(): void
    {
        // Create instance
        $obj = AnalysisProfile::create("Profile")->prep(Analyzer::takeSnapshot());
        // Write a Record
        $record = $obj->start("Record");
        // Close
        $stopped = $obj->stop($record->getUID());

        // Check if $this->stop() return a AnalysisRecord
        $this->assertInstanceOf(AnalysisRecord::class, $stopped);
        // Check if $this->stop() some non-exist UID will return null
        $this->assertNull($obj->stop("123546879987654321"));
    }

    public function testCanEstablishRelationForRecordsWhileWritingInSameProfile(): void
    {
        // Create Profile
        $profile = Analyzer::profile("Profile");

        // Write Record
        $activeRecord = $profile->prep(Analyzer::takeSnapshot())->start("Active record");
        // "Active record" won't have any relation
        $this->assertEmpty($activeRecord->getRelations());

        // Write and stop Record
        $inactiveRecord = $profile->prep(Analyzer::takeSnapshot())->start("Inactive record")->stop();

        // *********************************************************************************************
        // Since "Inactive record" is start when "Active record" is recording
        // An intersect relation is established between $activeRecord and $inactiveRecord
        // *********************************************************************************************

        // Get relation
        $relation = $activeRecord->getRelations()[0];

        // Check if both relation point to same instance
        $this->assertSame($relation, $inactiveRecord->getRelations()[0]);
        // Check if relation is not intersect
        $this->assertFalse($relation->isIntersect());

        // *********************************************************************************************

        // Add "Intersect record" to Profile
        $intersectRecord = $profile->prep(Analyzer::takeSnapshot())->start("Intersect record");

        // *********************************************************************************************
        // Since "Intersect record" is start when "Active record" is recording
        // An intersect relation is established between $activeRecord and $inactiveRecord
        // But "Inactive record" is already stopped, so no relation between "Inactive record" and "Intersect record"
        // *********************************************************************************************

        // Get relation (now at index 1 of "Active record"
        $relation = $activeRecord->getRelations()[1];

        // Check if both relation point to same instance
        $this->assertSame($relation, $intersectRecord->getRelations()[0]);
        // Check if relation is not intersect
        $this->assertFalse($relation->isIntersect());

        // Check if no relation between "Inactive record" and "Intersect record"
        // The only relation is between itself and "Active record"
        $this->assertCount(1, $inactiveRecord->getRelations());
        // The only relation is between itself and "Active record"
        $this->assertCount(1, $intersectRecord->getRelations());

        // *********************************************************************************************

        // Close "Active record" first and then "Intersect record"
        $activeRecord->stop();
        $intersectRecord->stop();

        // *********************************************************************************************
        // Since "Intersect record" is stopped after "Active record"
        // Their relation will change from ownership to intersect
        // *********************************************************************************************

        // Get relation (now at index 1 of "Active record"
        $relation = $activeRecord->getRelations()[1];
        // Check if both relation point to same instance
        $this->assertSame($relation, $intersectRecord->getRelations()[0]);
        // Check if relation is not intersect
        $this->assertTrue($relation->isIntersect());
    }

    public function testCanEstablishRelationForRecordsWhileWritingInMultipleProfile(): void
    {
        // *********************************************************************************************
        // This case has the same scenario as testCanEstablishRelationForWrittenRecordAndActiveRecordsInSameProfile()
        // The only difference is that Records will belong to different Profiles
        // It should produce the same result as testCanEstablishRelationForWrittenRecordAndActiveRecordsInSameProfile()
        // *********************************************************************************************

        // Since Analyzer is static, clear it first
        Analyzer::clear();
        // Create Profile (by using Analyzer)
        $profile1 = Analyzer::profile("Profile 1");
        $profile2 = Analyzer::profile("Profile 2");
        $profile3 = Analyzer::profile("Profile 3");

        // Write Record
        $activeRecord = $profile1->prep(Analyzer::takeSnapshot())->start("Active record");
        // "Active record" won't have any relation
        $this->assertEmpty($activeRecord->getRelations());

        // Write and stop Record
        $inactiveRecord = $profile2->prep(Analyzer::takeSnapshot())->start("Inactive record")->stop();

        // *********************************************************************************************
        // Since "Inactive record" is start when "Active record" is recording
        // An intersect relation is established between $activeRecord and $inactiveRecord
        // *********************************************************************************************

        // Get relation
        $relation = $activeRecord->getRelations()[0];

        // Check if both relation point to same instance
        $this->assertSame($relation, $inactiveRecord->getRelations()[0]);
        // Check if relation is not intersect
        $this->assertFalse($relation->isIntersect());

        // *********************************************************************************************

        // Add "Intersect record" to Profile
        $intersectRecord = $profile3->prep(Analyzer::takeSnapshot())->start("Intersect record");

        // *********************************************************************************************
        // Since "Intersect record" is start when "Active record" is recording
        // An intersect relation is established between $activeRecord and $inactiveRecord
        // But "Inactive record" is already stopped, so no relation between "Inactive record" and "Intersect record"
        // *********************************************************************************************

        // Get relation (now at index 1 of "Active record"
        $relation = $activeRecord->getRelations()[1];

        // Check if both relation point to same instance
        $this->assertSame($relation, $intersectRecord->getRelations()[0]);
        // Check if relation is not intersect
        $this->assertFalse($relation->isIntersect());
        // Check if no relation between "Inactive record" and "Intersect record"
        $this->assertEmpty($inactiveRecord->getRelations());
        // The only relation is between itself and "Active record"
        $this->assertCount(1, $intersectRecord->getRelations());

        // *********************************************************************************************

        // Close "Active record" first and then "Intersect record"
        $activeRecord->stop();
        $intersectRecord->stop();

        // *********************************************************************************************
        // Since "Intersect record" is stopped after "Active record"
        // Their relation will change from ownership to intersect
        // *********************************************************************************************

        // Get relation (now at index 1 of "Active record"
        $relation = $activeRecord->getRelations()[1];
        // Check if both relation point to same instance
        $this->assertSame($relation, $intersectRecord->getRelations()[0]);
        // Check if relation is not intersect
        $this->assertTrue($relation->isIntersect());
    }
}
