<?php

namespace Duckster\Analyzer\Tests;

use Duckster\Analyzer\AnalyzerConfig;
use Duckster\Analyzer\Tests\Config\InvalidRecordInstanceConfig;
use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\AnalyzerEntry;
use Duckster\Analyzer\Structures\AnalysisProfile;
use PHPUnit\Framework\TestCase;

class AnalyzerEntryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Analyzer::tryToInit(new AnalyzerConfig());
        Analyzer::clear();
    }

    public function testCanBeConstructed(): void
    {
        // Create instance
        $entry = Analyzer::profile("Profile");

        // Check instance class
        $this->assertInstanceOf(AnalyzerEntry::class, $entry);
        // Check if entry can get Profile
        $this->assertInstanceOf(AnalysisProfile::class, $entry->getProfile());
        // Check if Profile's name
        $this->assertSame("Profile", $entry->getProfile()->getName());
    }

    public function testCanStart(): void
    {
        // Create entry
        $entry = Analyzer::profile("Profile");

        // Check if entry's Profile is empty
        $this->assertEmpty($entry->getProfile()->getRecords());

        // Start
        $uid = $entry->start("Record 1");

        // Check size after start
        $this->assertCount(1, $entry->getProfile()->getRecords());
        // Check return type
        $this->assertIsString($uid);
        // Check Record's props
        $this->assertSame("Record 1", $entry->getProfile()->get($uid)->getName());
        $this->assertTrue($entry->getProfile()->get($uid)->isStarted());
        $this->assertFalse($entry->getProfile()->get($uid)->isStopped());
    }

    public function testCanReturnEmptyStringIfStartWithInvalidRecordInstance(): void
    {
        Analyzer::tryToInit(new InvalidRecordInstanceConfig());

        // Return empty string
        $this->assertEmpty(Analyzer::profile("Profile")->start());
    }

    public function testCanStop(): void
    {
        // Create entry
        $entry = Analyzer::profile("Profile");
        // Start
        $uid = $entry->start("Record 2");
        $entry->stop($uid);

        // Check Record's props
        $this->assertSame("Record 2", $entry->getProfile()->get($uid)->getName());
        $this->assertFalse($entry->getProfile()->get($uid)->isStarted());
        $this->assertTrue($entry->getProfile()->get($uid)->isStopped());
    }

    public function testCanStopLatest(): void
    {
        // Create entry
        $entry = Analyzer::profile("Profile");
        // Start
        $uid1 = $entry->start("Record 1");
        $uid2 = $entry->start("Record 2");
        $entry->stop();

        // Check if Record 1 is not stopped
        $this->assertTrue($entry->getProfile()->get($uid1)->isStarted());
        $this->assertFalse($entry->getProfile()->get($uid1)->isStopped());
        // Check if Record 2 is stopped
        $this->assertFalse($entry->getProfile()->get($uid2)->isStarted());
        $this->assertTrue($entry->getProfile()->get($uid2)->isStopped());
    }

    public function testSubTestRelationWithSingleProfile(): void
    {
        $uid1 = Analyzer::profile("Profile")->start("Record 1");
        sleep(1);

        $uid2 = Analyzer::profile("Profile")->start("Record 2");
        sleep(1);
        Analyzer::profile("Profile")->stop();

        $uid3 = Analyzer::profile("Profile")->start("Record 3");
        sleep(2);
        Analyzer::profile("Profile")->stop($uid1);

        Analyzer::profile("Profile")->stop($uid3);

        // Get Record instance
        $record1 = Analyzer::getProfiles()['Profile']->get($uid1);
        $record2 = Analyzer::getProfiles()['Profile']->get($uid2);
        $record3 = Analyzer::getProfiles()['Profile']->get($uid3);

        // ***********************
        // Check Record 1
        // ***********************
        // Record is stopped
        $this->assertTrue($record1->isStopped());
        // Record has 2 relation
        $this->assertCount(2, $record1->getRelations());
        // Check relation owner
        $this->assertSame($record1, $record1->getRelations()[0]->getOwner());
        $this->assertSame($record1, $record1->getRelations()[1]->getOwner());
        // Check relation target
        $this->assertSame($record2, $record1->getRelations()[0]->getTarget());
        $this->assertSame($record3, $record1->getRelations()[1]->getTarget());
        // Check relation's type
        $this->assertFalse($record1->getRelations()[0]->isIntersect());
        $this->assertTrue($record1->getRelations()[1]->isIntersect());

        // ***********************
        // Check Record 2
        // ***********************

        // Record is stopped
        $this->assertTrue($record2->isStopped());
        // Record has 1 relation
        $this->assertCount(1, $record2->getRelations());
        // Check relation owner
        $this->assertSame($record1, $record2->getRelations()[0]->getOwner());
        // Check relation target
        $this->assertSame($record2, $record2->getRelations()[0]->getTarget());
        // Check relation's type
        $this->assertFalse($record2->getRelations()[0]->isIntersect());

        // ***********************
        // Check Record 3
        // ***********************

        // Record is stopped
        $this->assertTrue($record3->isStopped());
        // Record has 1 relation
        $this->assertCount(1, $record3->getRelations());
        // Check relation owner
        $this->assertSame($record1, $record3->getRelations()[0]->getOwner());
        // Check relation target
        $this->assertSame($record3, $record3->getRelations()[0]->getTarget());
        // Check relation's type
        $this->assertTrue($record3->getRelations()[0]->isIntersect());
    }

    public function testSubTestRelationWithMultipleProfile(): void
    {
        $uid1 = Analyzer::profile("Profile")->start("Record 1");
        sleep(1);

        $uid2 = Analyzer::profile("Child")->start("Record 2");
        sleep(1);
        Analyzer::profile("Child")->stop();

        $uid3 = Analyzer::profile("Intersect")->start("Record 3");
        sleep(2);
        Analyzer::profile("Profile")->stop();

        Analyzer::profile("Intersect")->stop();

        // Get Record instance
        $record1 = Analyzer::getProfiles()['Profile']->get($uid1);
        $record2 = Analyzer::getProfiles()['Child']->get($uid2);
        $record3 = Analyzer::getProfiles()['Intersect']->get($uid3);

        // ***********************
        // Check Record 1
        // ***********************
        // Record is stopped
        $this->assertTrue($record1->isStopped());
        // Record has 2 relation
        $this->assertCount(2, $record1->getRelations());
        // Check relation owner
        $this->assertSame($record1, $record1->getRelations()[0]->getOwner());
        $this->assertSame($record1, $record1->getRelations()[1]->getOwner());
        // Check relation target
        $this->assertSame($record2, $record1->getRelations()[0]->getTarget());
        $this->assertSame($record3, $record1->getRelations()[1]->getTarget());
        // Check relation's type
        $this->assertFalse($record1->getRelations()[0]->isIntersect());
        $this->assertTrue($record1->getRelations()[1]->isIntersect());

        // ***********************
        // Check Record 2
        // ***********************

        // Record is stopped
        $this->assertTrue($record2->isStopped());
        // Record has 1 relation
        $this->assertCount(1, $record2->getRelations());
        // Check relation owner
        $this->assertSame($record1, $record2->getRelations()[0]->getOwner());
        // Check relation target
        $this->assertSame($record2, $record2->getRelations()[0]->getTarget());
        // Check relation's type
        $this->assertFalse($record2->getRelations()[0]->isIntersect());

        // ***********************
        // Check Record 3
        // ***********************

        // Record is stopped
        $this->assertTrue($record3->isStopped());
        // Record has 1 relation
        $this->assertCount(1, $record3->getRelations());
        // Check relation owner
        $this->assertSame($record1, $record3->getRelations()[0]->getOwner());
        // Check relation target
        $this->assertSame($record3, $record3->getRelations()[0]->getTarget());
        // Check relation's type
        $this->assertTrue($record3->getRelations()[0]->isIntersect());
    }
}
