<?php

namespace Duckster\Analyzer\Interfaces;

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
     * Get Record by UID. Return null if $uid not found
     *
     * @param string $uid
     * @return IARecord|null
     */
    public function get(string $uid): ?IARecord;

    /**
     * Close and get record. Return null if close failed
     *
     * @param string $uid
     * @return IARecord|null
     */
    public function stop(string $uid): ?IARecord;
}
