<?php

namespace Duckster\Analyzer;

use Duckster\Analyzer\Interfaces\IARecord;
use Duckster\Analyzer\Structures\AnalysisProfile;
use Duckster\Analyzer\Structures\AnalysisRecord;
use Exception;

class AnalyzerEntry
{
    // ***************************************
    // Properties
    // ***************************************

    /**
     * @var AnalysisProfile Entry's Profile
     */
    private AnalysisProfile $profile;

    /**
     * @var AnalysisRecord Entry's Record
     */
    private AnalysisRecord $record;

    // ***************************************
    // Public API
    // ***************************************

    /**
     * Get Profiles
     *
     * @param string|null $title
     * @return IARecord
     */
    public function start(?string $title = null): IARecord
    {
        // Create a Record
        $this->record = AnalysisRecord::open(Analyzer::getTitle($title));
        // Get Analyzer's active Profile
        $activeProfile = array_filter(Analyzer::getProfiles(), [AnalysisProfile::class, 'isActive']);

        // Add Record to Profile and start recording
        return $this->profile->start($this->record, $activeProfile);
    }
}
