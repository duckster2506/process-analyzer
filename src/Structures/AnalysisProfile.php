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
     * @var int Profile's total memory usage
     */
    private int $usage;

    /**
     * @var array Memory footprints
     */
    private array $memFootprints;

    /**
     * @var IARecord[] Records
     */
    private array $records;

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
        $this->memFootprints = [];
        $this->usage = 0;
    }

    /**
     * Create a Profile
     */
    public static function create(string $name): AnalysisProfile
    {
        // Get memory before instantiation
        $localMem = memory_get_usage();
        // Create instance
        $output = new AnalysisProfile($name);
        // Get memory usage
        $output->usage = memory_get_usage() - $localMem;

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
     * Get Profile's memory usage
     *
     * @return int
     */
    public function getUsage(): int
    {
        return $this->usage;
    }

    /**
     * Get memory footprints
     *
     * @return array
     */
    public function getMemoryFootprints(): array
    {
        return $this->memFootprints;
    }

    /**
     * Get list of records
     *
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * Write a Record and return Record's UID
     *
     * @param string $name
     * @return string
     */
    public function write(string $name): string
    {
        return $this->put(AnalysisRecord::open($name));
    }

    /**
     * Put a Record into Profile. Replace Record if it's UID is already exists
     *
     * @param IARecord $record
     * @return string
     */
    public function put(IARecord $record): string
    {
        // Memory before execution
        $initMemory = memory_get_usage();
        // Get Record's UID
        $uid = $record->getUID();
        // Add temporary footprint
        $this->memFootprints[$uid] = $initMemory;
        // Create new Record and push to list
        $this->records[$uid] = $record;

        // Calculate used memory
        $used = memory_get_usage() - $initMemory + $record->getUsage();
        // Get execution memory usage
        $this->usage += $used;
        // Update footprint
        $this->memFootprints[$uid] = $used;

        // Start recording
        $this->records[$uid]->start();

        return $uid;;
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

    public function __toString(): string
    {
        return "{" .
            "name: " . $this->name . "," .
            " usage: " . $this->usage . "," .
            " footprints: " . json_encode($this->memFootprints) .
            "}";
    }
}