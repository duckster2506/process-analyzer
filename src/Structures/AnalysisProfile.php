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
     * @var IARecord[] Records
     */
    private array $records;

    /**
     * @var array Snapshot before execution
     */
    private ?array $snapshot;

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
        $this->snapshot = null;
    }

    /**
     * Prepare Profile
     */
    public static function create(string $name): AnalysisProfile
    {
        return new AnalysisProfile($name);
    }

    /**
     * Prepare Profile
     *
     * @param array|null $snapshot Set to null to make Profile unprepared
     * @return $this
     */
    public function prep(?array $snapshot): AnalysisProfile
    {
        // Save $snapshot
        $this->snapshot = $snapshot;

        return $this;
    }

    /**
     * Write a Record and return Record's UID
     *
     * @param string $name
     * @return string
     */
    public function write(string $name): string
    {
        // Create and put Record in
        $uid = $this->put(AnalysisRecord::open($name));
        // Start recording
        $this->records[$uid]->start();

        return $uid;
    }

    /**
     * Put a Record into Profile. Replace Record if it's UID is already exists
     *
     * @param IARecord $record
     * @return string
     * @throws \Exception
     */
    public function put(IARecord $record): string
    {
        // Check if Profile is prepared
        if (is_null($this->snapshot)) {
            throw new \Exception("Profile is not ready yet");
        }

        // Set Record's preSnapshot
        $record->setPreSnapshot($this->snapshot);
        // Get Record's UID
        $uid = $record->getUID();
        // Create new Record and push to list
        $this->records[$uid] = $record;
        // Clear Profile's prep snapshot
        $this->snapshot = null;

        return $uid;
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

        // Close and get Record
        $output = $output->close();
        // Replace
        $this->records[$uid] = $output;

        return $output;
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
     * Current snapshot
     *
     * @return array|null
     */
    public function getSnapshot(): ?array
    {
        return $this->snapshot;
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
