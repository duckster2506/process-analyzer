<?php

namespace Duckster\Analyzer\Interfaces;

use Exception;

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
     * @return string
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
     * @param string $name
     * @param IAProfile[] $activeProfiles
     * @return IARecord
     * @throws Exception
     */
    public function start(IARecord $record, array $activeProfiles): IARecord;

    /**
     * Close and get Record. Return null if stop failed
     *
     * @param string $uid
     * @return IARecord|null
     * @throws Exception
     */
    public function stop(string $uid): ?IARecord;

    /**
     * Get Profile's name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if Profile is active (has un-stopped Record)
     *
     * @return bool
     */
    public function isActive(): bool;
}
