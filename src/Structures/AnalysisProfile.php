<?php

namespace Duckster\Analyzer\Structures;

class AnalysisProfile
{
    /**
     * @var array Records
     */
    private array $records;

    /**
     * @var int Profile's total memory usage
     */
    private int $usage;

    /**
     * Create a Profile
     */
    public static function create(): AnalysisProfile
    {
        // Get memory before instantiation
        $localMem = memory_get_usage();

        // Create instance
        $output = new AnalysisProfile();
        $output->records = [];

        // Get memory usage
        $output->usage = memory_get_usage() - $localMem;

        return $output;
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
        // Create new Record and push to list
        $this->records[$uid] = AnalysisRecord::open();

        // Get execution memory usage
        $this->usage += (memory_get_usage() - $localMem);

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
}