<?php

namespace Duckster\Analyzer\Structures;

use Duckster\Analyzer\Analyzer;
use Duckster\Analyzer\Interfaces\IARecord;

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
     * @var float Mark start of preparation (pre execution) before start of recording
     */
    private array $preSnapshot;

    /**
     * @var float Mark start of recording
     */
    private array $startSnapshot;

    /**
     * @var float Mark end of recording
     */
    private array $endSnapshot;

    /**
     * @var array Mark end of preparation (post execution) after end of recording
     */
    private array $postSnapshot;

    /**
     * @var int Record status
     */
    private int $status;

    /**
     * @var bool Indicate if this Record is shared between multiple Profile
     */
    private bool $isShared;

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
        $this->preSnapshot = ['time' => 0.0, 'mem' => 0];
        $this->startSnapshot = ['time' => 0.0, 'mem' => 0];
        $this->endSnapshot = ['time' => 0.0, 'mem' => 0];
        $this->postSnapshot = ['time' => 0.0, 'mem' => 0];
        $this->status = 0;
        $this->isShared = false;
        $this->relations = [];
    }

    /**
     * Open a record
     *
     * @param string $name
     * @param bool $isShared
     * @return AnalysisRecord
     */
    public static function open(string $name, bool $isShared = false): AnalysisRecord
    {
        // Create instance
        $output = new AnalysisRecord($name);
        $output->uid = uniqid();
        $output->isShared = $isShared;

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
     * @param bool $isShared
     * @return AnalysisRecord|null
     */
    public function stop(): AnalysisRecord
    {
        // Create snapshot
        $snapshot = Analyzer::takeSnapshot();

        if ($this->status === 2) return $this;

        $output = $this;
        // Set status
        $output->status = 2;
        // Save end timestamp
        $output->endSnapshot = $snapshot;
        // Check relations
        $this->checkRelations();

        return $output;
    }

    /**
     * Copy Record
     *
     * @return AnalysisRecord
     */
    public function copy(): AnalysisRecord
    {
        $output = clone $this;

        // Change UID
        $output->uid = uniqid();
        // Set as non-shared
        $output->isShared = false;

        // Clear relations
        $output->relations = [];
        // Iterate through each relation
        foreach ($this->relations as $relation) {
            // Create new Relation
            $copiedRelation = new RecordRelation(...($relation->getOwner() === $this
                ? [$output, $relation->getTarget()]
                : [$relation->getOwner(), $output]));
            // Set relation's type
            if ($relation->isIntersect()) $copiedRelation->intersect();
            // Add to clone list
            $output->relations[] = $copiedRelation;
        }

        return $output;
    }

    /**
     * Get the preparation time to start and stop this Record
     *
     * @return float
     */
    public function prepTime(): float
    {
        // Get the preparation time before start recording
        $preTime = ($this->startSnapshot['time'] ?? 0.0) - ($this->preSnapshot['time'] ?? 0.0);
        // Get the preparation time after stop recording
        $postTime = ($this->postSnapshot['time'] ?? 0.0) - ($this->endSnapshot['time'] ?? 0.0);

        return $preTime + $postTime;
    }

    /**
     * Get the time diff of recording
     *
     * @return float
     */
    public function diffTime(): float
    {
        return ($this->startSnapshot['time'] === 0.0 || $this->endSnapshot['time'] === 0.0)
            ? 0.0
            : $this->endSnapshot['time'] - $this->startSnapshot['time'];
    }

    /**
     * Get the preparation memory to start and stop this Record
     *
     * @return int
     */
    public function prepMem(): int
    {
        // Get the preparation memory before start recording
        $preTime = ($this->startSnapshot['mem'] ?? 0) - ($this->preSnapshot['mem'] ?? 0);
        // Get the preparation memory after stop recording
        $postTime = ($this->postSnapshot['mem'] ?? 0) - ($this->endSnapshot['mem'] ?? 0);

        return $preTime + $postTime;
    }

    /**
     * Get the memory diff of recording
     *
     * @return int
     */
    public function diffMem(): int
    {
        return ($this->startSnapshot['mem'] === 0 || $this->endSnapshot['mem'] === 0)
            ? 0
            : $this->endSnapshot['mem'] - $this->startSnapshot['mem'];
    }

    /**
     * Set pre snapshot and return self
     *
     * @param array $snapshot
     * @return $this
     */
    public function setPreSnapshot(array $snapshot): AnalysisRecord
    {
        $this->preSnapshot = $snapshot;

        return $this;
    }

    /**
     * Set post snapshot and return self
     *
     * @param array $snapshot
     * @return $this
     */
    public function setPostSnapshot(array $snapshot): AnalysisRecord
    {
        $this->postSnapshot = $snapshot;

        return $this;
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
     * Get endTime timestamp
     *
     * @return float
     */
    public function getEndTime(): float
    {
        return $this->endSnapshot['time'] ?? 0.0;
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
     * Get end emalloc() memory usage
     *
     * @return int
     */
    public function getEndMem(): int
    {
        return $this->endSnapshot['mem'] ?? 0;
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
     * Check if Record is shared
     *
     * @return bool
     */
    public function isShared(): bool
    {
        return $this->isShared;
    }

    /**
     * Get pre start snapshot
     *
     * @return array
     */
    public function getPreSnapshot(): array
    {
        return $this->preSnapshot;
    }

    /**
     * Get post end snapshot
     *
     * @return array
     */
    public function getPostSnapshot(): array
    {
        return $this->postSnapshot;
    }

    public function __toString(): string
    {
        return "{" .
            "startTime: " . $this->getStartTime() . "," .
            " endTime: " . $this->getEndTime() . "," .
            " startMem: " . $this->getStartMem() . " bytes," .
            " endMem: " . $this->getEndMem() . " bytes," .
            " status: " . ($this->isStarted() ? "Started" : ($this->isStopped() ? "Closed" : "Pending")) .
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
