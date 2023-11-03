<?php

namespace Duckster\Analyzer\Interfaces;

use Duckster\Analyzer\Structures\AnalysisRecord;

interface IAProfile
{
    /**
     * Create a Profile
     *
     * @param string $name
     * @return IAProfile
     */
    public static function create(string $name): IAProfile;

    /**
     * Put a Record into Profile. Replace Record if it's UID is already exists
     *
     * @param IARecord $record
     * @return IARecord
     */
    public function put(IARecord $record): IARecord;

    /**
     * Get Record by UID. Return null if $uid not found
     *
     * @param string $uid
     * @return IARecord|null
     */
    public function get(string $uid): ?IARecord;

    /**
     * Start and get Record. Return null if start failed
     *
     * @param IARecord $record
     * @param IAProfile[]|null $activeProfiles
     * @param array $extras
     * @return IARecord
     */
    public function start(IARecord $record, array $activeProfiles = null, array $extras = []): IARecord;

    /**
     * Stop and get Record. Return null if stop failed
     *
     * @param string $uid
     * @param array $extras
     * @return IARecord|null
     */
    public function stop(string $uid, array $extras = []): ?IARecord;

    /**
     * Get the latest active Record
     *
     * @return IARecord|null
     */
    public function getLatestActiveRecord(): ?IARecord;

    /**
     * Get Profile's name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get records
     *
     * @return AnalysisRecord[]
     */
    public function getRecords(): array;

    /**
     * Check if Profile is active (has un-stopped Record)
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Set name
     *
     * @param string $name
     * @return IAProfile
     */
    public function setName(string $name): IAProfile;
}
