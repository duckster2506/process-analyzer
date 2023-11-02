<?php

namespace Duckster\Analyzer\Structures;

use Duckster\Analyzer\Interfaces\IAProfile;
use Duckster\Analyzer\Interfaces\IARecord;

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

    /**
     * @var array Stopped Records
     */
    private array $stopped;

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
        $this->stopped = [];
    }

    /**
     * Prepare Profile
     */
    public static function create(string $name): AnalysisProfile
    {
        return new AnalysisProfile($name);
    }

    /**
     * Setup new Record's relation
     *
     * @param IARecord $record
     * @param AnalysisProfile[] $profiles
     * @return void
     */
    public static function setupRecordRelation(IARecord $record, array $profiles): void
    {
        foreach ($profiles as $profile) {
            // Check if Profile is active
            if ($profile->isActive()) {
                // Iterate through each Profile's active Record
                foreach ($profile->activeIds as $activeId => $value) {
                    // Check if $activeId exists in $records
                    if (array_key_exists($activeId, $profile->records)) {
                        // Add $record to activeIds relation list
                        $profile->records[$activeId]->establishRelation($record);
                    }
                }
            }
        }
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
     * Put and start a Record
     *
     * @param IARecord $record
     * @param AnalysisProfile[]|null $activeProfiles
     * @return IARecord
     */
    public function start(IARecord $record, ?array $activeProfiles = null): IARecord
    {
        // Put $record into the list
        $this->put($record);
        // Setup $record relation
        static::setupRecordRelation($record, $activeProfiles ?? [$this]);
        // Add this Record to active list
        $this->activeIds[$record->getUID()] = true;

        return $record->start();
    }

    /**
     * Stop and get record. Return null if stop failed
     *
     * @param string $uid
     * @return IARecord|null
     */
    public function stop(string $uid): ?IARecord
    {
        // Get Record by UID
        $output = $this->records[$uid] ?? null;
        if (is_null($output)) return null;
        // Remove this out of active list
        unset($this->activeIds[$uid]);
        $this->stopped[$uid] = true;

        // stop
        return $output->stop();
    }

    /**
     * Get the latest active Record
     *
     * @return IARecord|null
     */
    public function getLatestActiveRecord(): ?IARecord
    {
        // Get key
        $key = array_key_last($this->activeIds);
        if (is_null($key)) return null;

        return $this->records[$key];
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

    /**
     * Set name
     *
     * @param string $name
     * @return IAProfile
     */
    public function setName(string $name): IAProfile
    {
        $this->name = $name;

        return $this;
    }

    public function __toString(): string
    {
        return "{name: $this->name}";
    }
}
