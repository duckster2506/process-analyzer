<?php

namespace Duckster\Analyzer\Structures;

class AnalysisRecord
{
    // ***************************************
    // Properties
    // ***************************************

    /**
     * @var string Record's name
     */
    private string $name;

    /**
     * @var float Start timestamp
     */
    private float $startTime;

    /**
     * @var float End timestamp
     */
    private float $endTime;

    /**
     * @var int Real memory usage
     */
    private int $realMem;

    /**
     * @var int start emalloc() memory usage
     */
    private int $startEmMem;

    /**
     * @var int end emalloc() memory usage
     */
    private int $endEmMem;

    /**
     * @var int Real peak memory
     */
    private int $realPeak;

    /**
     * @var int Peak emalloc() memory
     */
    private int $emPeak;

    /**
     * @var int Self usage memory
     */
    private int $usage;

    /**
     * @var bool Record status
     */
    private int $status;

    // ***************************************
    // Public API
    // ***************************************

    /**
     * Constructor
     */
    public function __construct(string $name, int $initMemory)
    {
        $this->name = $name;
        $this->startTime = hrtime(true) / 1e+6;
        $this->endTime = hrtime(true) / 1e+6;
        $this->usage = 0;
        $this->startEmMem = $initMemory;
        $this->endEmMem = $initMemory;
        $this->status = 0;
        $this->fetchMemory();
    }

    /**
     * Open a record
     *
     * @param string $name
     * @return AnalysisRecord
     */
    public static function open(string $name): AnalysisRecord
    {
        // Get memory before instantiation
        $localMem = memory_get_usage();

        // Create instance
        $output = new AnalysisRecord($name, $localMem);

        // Calculate instantiation memory usage
        $output->usage = memory_get_usage() - $localMem;

        return $output;
    }

    /**
     * Start recording
     *
     * @return AnalysisRecord
     */
    public function start(): AnalysisRecord
    {
        if ($this->status === 1) {
            return $this;
        }

        // Set status
        $this->status = 1;
        // Reset $startTime
        $this->startTime = hrtime(true) / 1e+6;

        return $this;
    }

    /**
     * Close
     *
     * @return AnalysisRecord|null
     */
    public function close(): ?AnalysisRecord
    {
        // Mark endTime timestamp
        $localEnd = hrtime(true) / 1e+6;

        if ($this->status === 2) {
            return null;
        }

        // Set status
        $this->status = 2;
        // Save end timestamp
        $this->endTime = $localEnd;
        // Mark endEmMem
        $this->endEmMem = memory_get_usage() - $this->usage;
        // Fetch
        $this->fetchMemory();

        return $this;
    }

    /**
     * Get Record's name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get startTime timestamp
     *
     * @return float
     */
    public function getStartTime(): float
    {
        return $this->startTime;
    }

    /**
     * Get endTime timestamp
     *
     * @return float
     */
    public function getEndTime(): float
    {
        return $this->endTime;
    }

    /**
     * Get real memory usage
     *
     * @return int
     */
    public function getRealMem(): int
    {
        return $this->realMem;
    }

    /**
     * Get start emalloc() memory usage
     *
     * @return int
     */
    public function getStartEmMem(): int
    {
        return $this->startEmMem;
    }

    /**
     * Get end emalloc() memory usage
     *
     * @return int
     */
    public function getEndEmMem(): int
    {
        return $this->endEmMem;
    }

    /**
     * Get real peak memory
     *
     * @return int
     */
    public function getRealPeak(): int
    {
        return $this->realPeak;
    }

    /**
     * Get emalloc() peak memory
     *
     * @return int
     */
    public function getEmPeak(): int
    {
        return $this->emPeak;
    }

    /**
     * Get self memory usage
     *
     * @return int
     */
    public function getUsage(): int
    {
        return $this->usage;
    }

    /**
     * Check if Record is closed
     *
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->status === 1;
    }

    public function isClosed(): bool
    {
        return $this->status === 2;
    }

    /**
     * Get the diff between startTime and endTime
     *
     * @return float
     */
    public function diffTime(): float
    {
        return $this->endTime - $this->startTime;
    }

    /**
     * Get the diff between startEmMem and endEmMem
     *
     * @return int
     */
    public function diffEmMem(): int
    {
        return $this->endEmMem - $this->startEmMem;
    }

    public function __toString(): string
    {
        return "{" .
            " startTime: " . $this->startTime . "," .
            " endTime: " . $this->endTime . "," .
            " realMem: " . $this->realMem . " bytes," .
            " startEmMem: " . $this->startEmMem . " bytes," .
            " endEmMem: " . $this->endEmMem . " bytes," .
            " realPeak: " . $this->realPeak . " bytes," .
            " emPeak: " . $this->emPeak . " bytes," .
            " usage: " . $this->usage . " bytes," .
            " status: " . ($this->isStarted() ? "Started" : ($this->isClosed() ? "Closed" : "Pending")) .
            "}";
    }

    /**
     * Fetch record's data
     *
     * @return void
     */
    private function fetchMemory(): void
    {
        $this->emPeak = memory_get_peak_usage();
        $this->realMem = memory_get_usage(true);
        $this->realPeak = memory_get_peak_usage(true);
    }
}