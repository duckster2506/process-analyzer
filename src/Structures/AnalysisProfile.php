<?php

namespace Duckster\Analyzer\Structures;

use Duckster\Analyzer\Utils;

class AnalysisProfile
{
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
     * @return string
     */
    public function write(): string
    {
        // Get memory before execution
        $localMem = memory_get_usage();

        // Create a unique id
        $uid = uniqid();
        // Add temporary footprint
        $this->memFootprints[$uid] = memory_get_usage() - $localMem;
        // Create new Record and push to list
        $this->records[$uid] = AnalysisRecord::open();

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
     * Get Record by UID
     *
     * @param string $uid
     * @return AnalysisRecord
     */
    public function get(string $uid): AnalysisRecord
    {
        return $this->records[$uid];
    }

    /**
     * Close and get record
     *
     * @param string $uid
     * @return AnalysisRecord
     */
    public function close(string $uid): AnalysisRecord
    {
        $record = $this->get($uid);
        $record->close();

        return $record;
    }

    public function __toString(): string
    {
        return "{" . PHP_EOL .
            "\tname: " . $this->name . "," . PHP_EOL .
            "\tusage: " . $this->usage . "," . PHP_EOL .
            "\tfootprints: " . print_r($this->memFootprints, true) . " bytes," . PHP_EOL .
            "}";
    }
}