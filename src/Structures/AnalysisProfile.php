<?php

namespace Duckster\Analyzer\Structures;

class AnalysisProfile
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
     * @var int Profile's total memory usage
     */
    private int $usage;

    /**
     * @var array Memory footprints
     */
    private array $memFootprints;

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
     * Get list of records
     *
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
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
     * Write a Record and return Record UID
     *
     * @param string $name
     * @return string
     */
    public function write(string $name): string
    {
        // Get memory before execution
        $localMem = memory_get_usage();

        // Create a unique id
        $uid = uniqid();
        // Add temporary footprint
        $this->memFootprints[$uid] = memory_get_usage() - $localMem;
        // Create new Record and push to list
        $this->records[$uid] = AnalysisRecord::open($name);

        // Calculate used memory
        $used = memory_get_usage() - $localMem;
        // Get execution memory usage
        $this->usage += $used;
        // Update footprint
        $this->memFootprints[$uid] = $used;

        // Start recording
        $this->records[$uid]->start();

        return $uid;
    }

    /**
     * Get Record by UID. Return null if $uid not found
     *
     * @param string $uid
     * @return AnalysisRecord|null
     */
    public function get(string $uid): ?AnalysisRecord
    {
        return $this->records[$uid] ?? null;
    }

    /**
     * Close and get record. Return null if close failed
     *
     * @param string $uid
     * @return AnalysisRecord|null
     */
    public function close(string $uid): ?AnalysisRecord
    {
        return $this->get($uid)?->close();
    }

    public function __toString(): string
    {
        return "{" .
            " name: " . $this->name . "," .
            " usage: " . $this->usage . "," .
            " footprints: " . print_r($this->memFootprints, true) . " bytes," .
            "}";
    }
}