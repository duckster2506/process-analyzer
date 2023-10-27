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
     * Setup new Record's relation
     *
     * @param IARecord $record
     * @param AnalysisProfile[] $profiles
     * @return void
     */
    public static function setupRecordRelation(IARecord $record, array $profiles): void
    {
        foreach ($profiles as $profile) {
            // Iterate through each Profile's active Record
            foreach ($profile->activeIds as $activeId => $value) {
                // Check if $activeId exists in $records
                if (array_key_exists($activeId, $profile->records)) {
                    // Check if the Record with $activeId is actually stopped
                    if ($profile->records[$activeId]->isStopped()) {
                        // Remove $activeId from $activeIds (use to take care shared Record)
                        unset($profile->activeIds[$activeId]);
                    } else {
                        // Add $record to activeIds relation list
                        $profile->records[$activeId]->establishRelation($record);
                    }
                }
            }
        }
    }

    /**
     * Put and start a Record
     *
     * @param IARecord $record
     * @param AnalysisProfile[]|null $activeProfiles
     * @return IARecord
     */
    public function start(IARecord $record, ?array $activeProfiles = null): AnalysisRecord
    {
        // Create and put Record to list and start
        return $this->startByUID($this->put($record)->getUID());
    }

    /**
     * Start by UID
     *
     * @param string $uid
     * @param AnalysisProfile[]|null $activeProfiles
     * @return AnalysisRecord|null
     */
    public function startByUID(string $uid, ?array $activeProfiles = null): ?AnalysisRecord
    {
        // Get $record
        $record = $this->get($uid);

        if (is_null($record)) return null;
        // Setup $record relation
        static::setupRecordRelation($record, $activeProfiles ?? [$this]);
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

        // Remove this out of active list
        unset($this->activeIds[$uid]);
        // Branch the Record if it's shared
        if ($output->isShared()) $output = $output->branch();
        // Replace
        $this->records[$uid] = $output->stop();

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
}
