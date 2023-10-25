<?php

namespace Duckster\Analyzer\Structures;

class RecordRelation
{
    // ***************************************
    // Properties
    // ***************************************

    /**
     * @var string Is Records intersect, if not, i'll be a parent-child relation
     */
    private bool $isIntersect;

    /**
     * @var AnalysisRecord Target Record
     */
    private AnalysisRecord $target;
}