<?php

namespace Duckster\Analyzer\Structures;

use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\Interfaces\IARecord;
use Exception;

class AnalysisRecord implements IARecord
{
    // ***************************************
    // Properties
    // ***************************************

    /**
     * @var string Record's UID
     */
    private string $uid;

    /**
     * @var string Record's name
     */
    private string $name;

    /**
     * @var array Mark preparation before start of recording
     */
    private array $preStartSnapshot;

    /**
     * @var array Mark start of recording
     */
    private array $startSnapshot;

    /**
     * @var array Mark preparation before stop of recording
     */
    private array $preStopSnapshot;

    /**
     * @var array Mark stop of recording
     */
    private array $stopSnapshot;

    /**
     * @var int Record status
     */
    private int $status;

    /**
     * @var RecordRelation[] $relations Record's relation
     */
    private array $relations;

    // ***************************************
    // Public API
    // ***************************************

    /**
     * Constructor
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->preStartSnapshot = ['time' => 0.0, 'mem' => 0];
        $this->startSnapshot = ['time' => 0.0, 'mem' => 0];
        $this->preStopSnapshot = ['time' => 0.0, 'mem' => 0];
        $this->stopSnapshot = ['time' => 0.0, 'mem' => 0];
        $this->status = 0;
        $this->relations = [];
    }

    /**
     * Open a record
     *
     * @param string $name
     * @param bool $isShared
     * @return AnalysisRecord
     */
    public static function open(string $name): AnalysisRecord
    {
        // Create instance
        $output = new AnalysisRecord($name);
        $output->uid = uniqid();

        return $output;
    }

    /**
     * Start recording
     *
     * @return AnalysisRecord
     */
    public function start(): AnalysisRecord
    {
        if ($this->status === 1) return $this;

        // Set status
        $this->status = 1;
        // Create start snapshot
        $this->startSnapshot = Analyzer::takeSnapshot(false);

        return $this;
    }

    /**
     * Stop recording
     *
     * @return AnalysisRecord
     */
    public function stop(): AnalysisRecord
    {
        // Get local pre stop snapshot
        $preStopSnapshot = Analyzer::takeSnapshot();
        if ($this->status === 2) return $this;

        // Check if preStopSnapshot is set
        if ($this->preStopSnapshot['time'] === 0.0) {
            $this->preStopSnapshot = $preStopSnapshot;
        }

        // Set status
        $this->status = 2;
        // Check relations
        $this->checkRelations();
        // Save stop timestamp
        $this->stopSnapshot = Analyzer::takeSnapshot(false);

        return $this;
    }

    /**
     * Get the preparation time to start this Record
     *
     * @return float
     */
    public function preStartPrepTime(): float
    {
        return ($this->startSnapshot['time'] ?? 0.0) - ($this->preStartSnapshot['time'] ?? 0.0);
    }

    /**
     * Get the preparation time to start and stop this Record
     *
     * @return float
     */
    public function prepTime(): float
    {
        return $this->preStartPrepTime() + $this->preStopPrepTime();
    }

    /**
     * Get the preparation time to stop this Record
     *
     * @return float
     */
    public function preStopPrepTime(): float
    {
        return ($this->stopSnapshot['time'] ?? 0.0) - ($this->preStopSnapshot['time'] ?? 0.0);
    }

    /**
     * Get the time diff of recording
     *
     * @return float
     */
    public function diffTime(): float
    {
        if ($this->startSnapshot['time'] === 0.0 || $this->stopSnapshot['time'] === 0.0) {
            return 0.0;
        }

        return $this->preStopSnapshot['time'] - $this->startSnapshot['time'];
    }

    /**
     * Get the preparation mem to start this Record
     *
     * @return int
     */
    public function preStartPrepMem(): int
    {
        return ($this->startSnapshot['mem'] ?? 0) - ($this->preStartSnapshot['mem'] ?? 0);
    }

    /**
     * Get the preparation memory to start and stop this Record
     *
     * @return int
     */
    public function prepMem(): int
    {
        return $this->preStartPrepMem() + $this->preStopPrepMem();
    }

    /**
     * Get the preparation mem to stop this Record
     *
     * @return int
     */
    public function preStopPrepMem(): int
    {
        return ($this->stopSnapshot['mem'] ?? 0) - ($this->preStopSnapshot['mem'] ?? 0);
    }

    /**
     * Get the memory diff of recording
     *
     * @return int
     */
    public function diffMem(): int
    {
        if ($this->startSnapshot['mem'] === 0 || $this->stopSnapshot['mem'] === 0) {
            return 0;
        }

        return $this->preStopSnapshot['mem'] - $this->startSnapshot['mem'];
    }

    /**
     * Establish a relation
     *
     * @param AnalysisRecord $record
     * @return void
     */
    public function establishRelation(AnalysisRecord $record): void
    {
        // Create a Relation
        $relation = new RecordRelation($this, $record);
        // Add to relation list
        $this->relations[] = $relation;
        $record->relations[] = $relation;
    }

    /**
     * Get Record's UID
     *
     * @return string
     */
    public function getUID(): string
    {
        return $this->uid;
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
        return $this->startSnapshot['time'] ?? 0.0;
    }

    /**
     * Get stopTime timestamp
     *
     * @return float
     */
    public function getStopTime(): float
    {
        return $this->stopSnapshot['time'] ?? 0.0;
    }

    /**
     * Get start emalloc() memory usage
     *
     * @return int
     */
    public function getStartMem(): int
    {
        return $this->startSnapshot['mem'] ?? 0;
    }

    /**
     * Get stop emalloc() memory usage
     *
     * @return int
     */
    public function getStopMem(): int
    {
        return $this->stopSnapshot['mem'] ?? 0;
    }

    /**
     * Get relations
     *
     * @return RecordRelation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * Check if Record is started
     *
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->status === 1;
    }

    /**
     * Check if Record is stopped
     *
     * @return bool
     */
    public function isStopped(): bool
    {
        return $this->status === 2;
    }

    /**
     * Get pre start snapshot
     *
     * @return array
     */
    public function getPreStartSnapshot(): array
    {
        return $this->preStartSnapshot;
    }

    /**
     * Get pre end snapshot
     *
     * @return array
     */
    public function getPreStopSnapshot(): array
    {
        return $this->preStopSnapshot;
    }

    /**
     * Set pre start snapshot and return self
     *
     * @param array $snapshot
     * @return $this
     */
    public function setPreStartSnapshot(array $snapshot): AnalysisRecord
    {
        $this->preStartSnapshot = $snapshot;

        return $this;
    }

    /**
     * Set pre stop snapshot and return self
     *
     * @param array $snapshot
     * @return $this
     */
    public function setPreStopSnapshot(array $snapshot): AnalysisRecord
    {
        $this->preStopSnapshot = $snapshot;

        return $this;
    }

    /**
     * Set start snapshot
     *
     * @param array $snapshot
     * @return AnalysisRecord
     */
    public function setStartSnapshot(array $snapshot): AnalysisRecord
    {
        $this->startSnapshot = $snapshot;
        return $this;
    }

    /**
     * Set stop snapshot
     *
     * @param array $snapshot
     * @return AnalysisRecord
     */
    public function setStopSnapshot(array $snapshot): AnalysisRecord
    {
        $this->stopSnapshot = $snapshot;
        return $this;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString(): string
    {
        return "{" .
            "uid: " . $this->getUID() . "," .
            " startTime: " . $this->getStartTime() . "," .
            " stopTime: " . $this->getStopTime() . "," .
            " startMem: " . $this->getStartMem() . " bytes," .
            " stopMem: " . $this->getStopMem() . " bytes," .
            " status: " . ($this->isStarted() ? "Started" : ($this->isStopped() ? "Stopped" : "Pending")) .
            "}";
    }

    // ***************************************
    // Private API
    // ***************************************

    /**
     * Iterate through each relation and intersect them if relation's target is stopped
     *
     * @return void
     */
    private function checkRelations(): void
    {
        // Iterate through each relation
        foreach ($this->relations as $relation) {
            // Check if $owner try to stop while $target is not stopped
            if ($relation->getOwner() === $this && $relation->getTarget()->isStarted()) {
                // Mark relation as intersect
                $relation->intersect();
            }
        }
    }
}
