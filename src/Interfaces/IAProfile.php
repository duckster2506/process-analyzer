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
     * Prepare Profile
     *
     * @param array|null $snapshot
     * @return IAProfile
     */
    public function prep(?array $snapshot): IAProfile;

    /**
     * Get Profile's name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get current snapshot
     *
     * @return array
     */
    public function getSnapshot(): ?array;

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
     * Start and get Record. Return null if close failed
     *
     * @param string $name
     * @return IARecord
     * @throws Exception
     */
    public function start(string $name): IARecord;

    /**
     * Close and get Record. Return null if close failed
     *
     * @param string $uid
     * @return IARecord|null
     * @throws Exception
     */
    public function stop(string $uid): ?IARecord;
}
