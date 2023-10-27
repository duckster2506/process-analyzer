<?php

namespace Duckster\Analyzer\Structures;

use Duckster\Analyzer\Interfaces\IAProfile;
use Duckster\Analyzer\Interfaces\IARecord;
use Exception;

class AnalysisProfile implements IAProfile
{
    // ***************************************
    // Properties
    // ***************************************

    /**
     * @var string Profile's name
     */
    private string $name;

    /**
     * @var AnalysisRecord[] Records
     */
    private array $records;

    /**
     * @var array Active Records
     */
    private array $activeIds;

    // ***************************************
    // Public API
    // ***************************************

    /**
     * Constructor
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->records = [];
        $this->activeIds = [];
    }

    /**
     * Prepare Profile
     */
    public static function create(string $name): AnalysisProfile
    {
        return new AnalysisProfile($name);
    }

    /**
     * Write a Record and return Record's UID
     *
     * @param IARecord $record
     * @param AnalysisProfile[]|null $activeProfiles
     * @return IARecord
     */
    public function start(IARecord $record, ?array $activeProfiles = null): AnalysisRecord
    {
        // Create and put Record to list
        $this->put($record);
        // Setup $record relation
        $this->setupRecordRelation($record, $activeProfiles);
        // Add this Record to active list
        $this->activeIds[$record->getUID()] = true;

        return $record->start();
    }

    /**
     * Put a Record into Profile. Replace Record if it's UID is already exists
     *
     * @param IARecord $record
     * @return IARecord
     */
    public function put(IARecord $record): IARecord
    {
        // Create new Record and push to list
        $this->records[$record->getUID()] = $record;

        return $record;
    }

    /**
     * Get Record by UID. Return null if $uid not found
     *
     * @param string $uid
     * @return IARecord|null
     */
    public function get(string $uid): ?IARecord
    {
        return $this->records[$uid] ?? null;
    }

    /**
     * Close and get record. Return null if close failed
     *
     * @param string $uid
     * @return IARecord|null
     */
    public function stop(string $uid): ?IARecord
    {
        // Get Record by UID
        $output = $this->records[$uid] ?? null;
        if (is_null($output)) return null;

        // Copy the Record if it's shared
        if ($output->isShared()) $output = $output->copy();
        // Replace
        $this->records[$uid] = $output->stop();
        // Remove this out of active list
        unset($this->activeIds[$uid]);

        return $output;
    }

    /**
     * Check if Profile is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return !empty($this->activeIds);
    }

    /**
     * Get Profile's name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get records
     *
     * @return AnalysisRecord[]
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    public function __toString(): string
    {
        return "{name: $this->name}";
    }

    // ***************************************
    // Private API
    // ***************************************

    /**
     * Setup new Record's relation
     *
     * @param IARecord $record
     * @param AnalysisProfile[]|null $activeProfiles
     * @return void
     */
    private function setupRecordRelation(IARecord $record, ?array $activeProfiles = null): void
    {
        foreach ($activeProfiles ?? [$this] as $profile) {
            // Iterate through each Profile's active Record
            foreach ($profile->activeIds as $activeIds => $value) {
                // Check if $activeIds exists in $records
                if (array_key_exists($activeIds, $this->records)) {
                    // Add $record to activeIds relation list
                    $this->records[$activeIds]->establishRelation($record);
                }
            }
        }
    }
}
