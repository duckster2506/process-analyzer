<?php

namespace Duckster\Analyzer\Interfaces;

use Duckster\Analyzer\Structures\AnalysisRecord;

interface IAProfile
{
    /**
     * Create a Profile
     */
    public static function create(string $name): IAProfile;

    /**
     * Get Profile's name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get list of records
     *
     * @return array
     */
    public function getRecords(): array;

    /**
     * Get Profile's memory usage
     *
     * @return int
     */
    public function getUsage(): int;

    /**
     * Get Profile's memory footprints
     *
     * @return array
     */
    public function getMemoryFootprints(): array;

    /**
     * Write a Record and return Record's UID
     *
     * @param string $name
     * @return string
     */
    public function write(string $name): string;

    /**
     * Put a Record into Profile. Replace Record if it's UID is already exists
     *
     * @param IARecord $record
     * @return string
     */
    public function put(IARecord $record): string;

    /**
     * Close and get record. Return null if close failed
     *
     * @param string $uid
     * @return IARecord|null
     */
    public function stop(string $uid): ?IARecord;
}