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

    /**
     * @var array Extra information
     */
    private array $extras;

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
        if ($this->status === 2) return $this;

        // Check if preStopSnapshot is set
        if ($this->preStopSnapshot['time'] === 0.0) {
            $this->preStopSnapshot = Analyzer::takeSnapshot();
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
        if (empty($this->startSnapshot['time']) || empty($this->preStartSnapshot['time'])) return 0.0;

        return $this->startSnapshot['time'] - $this->preStartSnapshot['time'];
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
        if (empty($this->stopSnapshot['time']) || empty($this->preStopSnapshot['time'])) return 0.0;

        return $this->stopSnapshot['time'] - $this->preStopSnapshot['time'];
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
     * Get the actual diff time (exclude all nested Record)
     *
     * @return float
     */
    public function actualTime(): float
    {
        return $this->calculateActual('time', 0.0);
    }

    /**
     * Get the preparation mem to start this Record
     *
     * @return int
     */
    public function preStartPrepMem(): int
    {
        if (empty($this->startSnapshot['mem']) || empty($this->preStartSnapshot['mem'])) return 0;

        return $this->startSnapshot['mem'] - $this->preStartSnapshot['mem'];
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
        if (empty($this->stopSnapshot['mem']) || empty($this->preStopSnapshot['mem'])) return 0;

        return $this->stopSnapshot['mem'] - $this->preStopSnapshot['mem'];
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
     * Get the actual diff mem (exclude all nested Record)
     *
     * @return int
     */
    public function actualMem(): int
    {
        return $this->calculateActual('mem', 0);
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
     * Get extra information
     *
     * @return array
     */
    public function getExtras(): array
    {
        return $this->extras;
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
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): AnalysisRecord
    {
        $this->name = $name;

        return $this;
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
            "\n\tuid: " . $this->getUID() . "," .
            "\n\tpreStartMem: " . $this->preStartSnapshot["mem"] . "," .
            "\n\tStartMem: " . $this->startSnapshot["mem"] . "," .
            "\n\tpreStopMem: " . $this->preStopSnapshot["mem"] . "," .
            "\n\tStopMem: " . $this->stopSnapshot["mem"] . "," .
            "\n\tpreStartPrepMem: " . $this->preStartPrepMem() . "," .
            "\n\tprepMem: " . $this->prepMem() . "," .
            "\n\tpreStopPrepMem: " . $this->preStopPrepMem() . "," .
            "\n\tdiffMem: " . $this->diffMem() . "," .
            "\n\tactualMem: " . $this->actualMem() . "," .
            "\n\tstatus: " . ($this->isStarted() ? "Started" : ($this->isStopped() ? "Stopped" : "Pending")) .
            "\n}";
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
            if ($relation->getOwner()->uid === $this->uid && $relation->getTarget()->isStarted()) {
                // Mark relation as intersect
                $relation->intersect();
            }
        }
    }

    /**
     * Common logic for calculate actual data
     *
     * @param string $key
     * @param int|float $init
     * @return int|float
     */
    private function calculateActual(string $key, int|float $init): int|float
    {
        // Capitalize $key
        $capitalized = ucfirst($key);
        // Get name of "diff" method
        $diffMethod = "diff" . $capitalized;
        // Get name of "prep" method
        $prepMethod = "prep" . $capitalized;
        // Get the name of preStartPrep method
        $preStartPrepMethod = "preStartPrep" . $capitalized;
        // Get the name of preStopPrep method
        $preStopPrepMethod = "preStopPrep" . $capitalized;

        // Iterate through each relation
        foreach ($this->relations as $relation) {
            // If $this is the owner of relation and relation's type is ownership
            if (!$relation->isIntersect() && $relation->getOwner()->uid === $this->uid) {
                // This means $relation->target prep time should be excluded
                $init += $relation->getTarget()->$prepMethod();
            } else if ($relation->isIntersect()) {
                // If relation's type is intersect, need to check if $this is the owner or target of the relation
                $init += $relation->getOwner()->uid === $this->uid
                    // If $this is the owner, exclude $relation->target preStart prep time out
                    ? $relation->getTarget()->$preStartPrepMethod()
                    // If $this is the target, exclude $relation->owner preStop prep time out
                    : $relation->getOwner()->$preStopPrepMethod();
            }
        }

        return $this->$diffMethod() - $init;
    }
}
