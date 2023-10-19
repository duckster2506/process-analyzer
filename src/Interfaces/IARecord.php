<?php

namespace Duckster\Analyzer\Interfaces;

interface IARecord
{
    /**
     * Open a record
     *
     * @param string $name
     * @param bool $isShared
     * @return IARecord
     */
    public static function open(string $name, bool $isShared = false): IARecord;

    /**
     * Start recording
     *
     * @return IARecord
     */
    public function start(): IARecord;

    /**
     * Close the Record.
     * If $isShared is true, stop Record and return self.
     * Else create another closed Record and return it
     *
     * @param bool $isShared
     * @return IARecord|null
     */
    public function close(bool $isShared = false): ?IARecord;

    /**
     * Stop recording (Close with $isShared is true)
     *
     * @return IARecord|null
     */
    public function stop(): ?IARecord;

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
     * Get startTime timestamp
     *
     * @return float
     */
    public function getStartTime(): float;


    /**
     * Get endTime timestamp
     *
     * @return float
     */
    public function getEndTime(): float;


    /**
     * Get real memory usage
     *
     * @return int
     */
    public function getRealMem(): int;


    /**
     * Get start emalloc() memory usage
     *
     * @return int
     */
    public function getStartEmMem(): int;


    /**
     * Get end emalloc() memory usage
     *
     * @return int
     */
    public function getEndEmMem(): int;


    /**
     * Get real peak memory
     *
     * @return int
     */
    public function getRealPeak(): int;


    /**
     * Get emalloc() peak memory
     *
     * @return int
     */
    public function getEmPeak(): int;


    /**
     * Get self memory usage
     *
     * @return int
     */
    public function getUsage(): int;


    /**
     * Check if Record is started
     *
     * @return bool
     */
    public function isStarted(): bool;


    /**
     * Check if Record is closed
     *
     * @return bool
     */
    public function isClosed(): bool;


    /**
     * Check if Record is shared
     *
     * @return bool
     */
    public function isShared(): bool;


    /**
     * Get the diff between startTime and endTime
     *
     * @return float
     */
    public function diffTime(): float;


    /**
     * Get the diff between startEmMem and endEmMem
     *
     * @return int
     */
    public function diffEmMem(): int;
}