<?php

namespace Duckster\Analyzer\Interfaces;

interface IARecord
{
    /**
     * Open a record
     *
     * @param string $name
     * @return IARecord
     */
    public static function open(string $name): IARecord;

    /**
     * Start recording
     *
     * @param array $extras
     * @return IARecord
     */
    public function start(array $extras = []): IARecord;

    /**
     * Stop recording.
     *
     * @param array $extras
     * @return IARecord
     */
    public function stop(array $extras = []): IARecord;

    /**
     * Get the actual diff time (exclude relation)
     *
     * @return float
     */
    public function actualTime(): float;

    /**
     * Get the actual diff mem (exclude relation)
     *
     * @return int
     */
    public function actualMem(): int;

    /**
     * Get Record's UID
     *
     * @return string
     */
    public function getUID(): string;

    /**
     * Get Record's name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get extra information
     *
     * @return array
     */
    public function getExtras(): array;

    /**
     * Check if Record is started
     *
     * @return bool
     */
    public function isStarted(): bool;


    /**
     * Check if Record is stopped
     *
     * @return bool
     */
    public function isStopped(): bool;

    /**
     * Set name
     *
     * @param string $name
     * @return IARecord
     */
    public function setName(string $name): IARecord;

    /**
     * Set pre snapshot and return
     *
     * @param array $snapshot
     * @return IARecord
     */
    public function setPreStartSnapshot(array $snapshot): IARecord;

    /**
     * Set post snapshot and return
     *
     * @param array $snapshot
     * @return IARecord
     */
    public function setPreStopSnapshot(array $snapshot): IARecord;
}
