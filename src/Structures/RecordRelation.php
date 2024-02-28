<?php

namespace Duckstery\Analyzer\Structures;

class RecordRelation
{
    // ***************************************
    // Properties
    // ***************************************

    /**
     * @var AnalysisRecord Owner Record
     */
    private AnalysisRecord $owner;

    /**
     * @var AnalysisRecord Target Record
     */
    private AnalysisRecord $target;

    /**
     * @var bool Is Records intersect, if not, it'll be an ownership relation
     */
    private bool $isIntersect;

    // ***************************************
    // Public API
    // ***************************************

    /**
     * Construct an ownership relation
     *
     * @param AnalysisRecord $owner
     * @param AnalysisRecord $record
     */
    public function __construct(AnalysisRecord $owner, AnalysisRecord $record)
    {
        $this->owner = $owner;
        $this->target = $record;
        $this->isIntersect = false;
    }

    /**
     * Mark this relation as intersect
     *
     * @return void
     */
    public function intersect(): void
    {
        $this->isIntersect = true;
    }

    /**
     * Check if this is an intersect relation
     *
     * @return bool
     */
    public function isIntersect(): bool
    {
        return $this->isIntersect;
    }

    /**
     * Get owner
     *
     * @return AnalysisRecord
     */
    public function getOwner(): AnalysisRecord
    {
        return $this->owner;
    }

    /**
     * Get target
     *
     * @return AnalysisRecord
     */
    public function getTarget(): AnalysisRecord
    {
        return $this->target;
    }
}
