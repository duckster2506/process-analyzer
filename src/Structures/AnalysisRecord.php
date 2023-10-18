<?php

namespace Duckster\Analyzer\Structures;

class AnalysisRecord
{
    /**
     * Start timestamp
     */
    private float $startTime;

    /**
     * End timestamp
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
     * Constructor
     */
    public function __construct(int $initMemory)
    {
        $this->startTime = hrtime(true) / 1e+6;
        $this->endTime = hrtime(true) / 1e+6;
        $this->usage = 0;
        $this->startEmMem = $initMemory;
        $this->endEmMem = $initMemory;
        $this->fetchMemory();
    }

    /**
     * Open a record
     *
     * @return AnalysisRecord
     */
    public static function open(): AnalysisRecord
    {
        // Get memory before instantiation
        $localMem = memory_get_usage();

        // Create instance
        $output = new AnalysisRecord($localMem);

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
        // Reset $startTime
        $this->startTime = hrtime(true) / 1e+6;

        return $this;
    }

    /**
     * Close
     *
     * @return void
     */
    public function close(): void
    {
        // Mark endTime timestamp
        $this->endTime = hrtime(true) / 1e+6;
        // Mark endEmMem
        $this->endEmMem = memory_get_usage() - $this->usage;
        // Fetch
        $this->fetchMemory();
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
     * Get the diff between startTime and endTime
     *
     * @return int
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
        return "{" . PHP_EOL .
            "\tstartTime: " . $this->startTime . "," . PHP_EOL .
            "\tendTime: " . $this->endTime . "," . PHP_EOL .
            "\trealMem: " . $this->realMem . " bytes," . PHP_EOL .
            "\tstartEmMem: " . $this->startEmMem . " bytes," . PHP_EOL .
            "\tendEmMem: " . $this->endEmMem . " bytes," . PHP_EOL .
            "\trealPeak: " . $this->realPeak . " bytes," . PHP_EOL .
            "\temPeak: " . $this->emPeak . " bytes," . PHP_EOL .
            "\tusage: " . $this->usage . " bytes," . PHP_EOL .
            "}";
    }

    /**
     * Fetch record's data
     *
     * @param bool $excludeUsage
     * @return void
     */
    private function fetchMemory(): void
    {
        $this->emPeak = memory_get_peak_usage();
        $this->realMem = memory_get_usage(true);
        $this->realPeak = memory_get_peak_usage(true);
    }
}