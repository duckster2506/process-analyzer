<?php

namespace Duckster\Analyzer;

use Duckster\Analyzer\Interfaces\IAProfile;
use Duckster\Analyzer\Structures\AnalysisProfile;
use Duckster\Analyzer\Structures\AnalysisRecord;

class AnalyzerEntry
{
    // ***************************************
    // Properties
    // ***************************************

    /**
     * @var array Pre snapshot
     */
    private array $snapshot;

    /**
     * @var IAProfile Entry's Profile
     */
    private IAProfile $profile;

    // ***************************************
    // Public API
    // ***************************************

    public function __construct(array $snapshot, IAProfile $profile)
    {
        $this->snapshot = $snapshot;
        $this->profile = $profile;
    }

    /**
     * Start recording
     *
     * @param string|null $title
     * @return string
     */
    public function start(?string $title = null): string
    {
        // Create a Record and set pre snapshot
        $record = AnalysisRecord::open(Analyzer::getTitle($title))->setPreStartSnapshot($this->snapshot);

        // Get Analyzer's Profile
        $activeProfile = Analyzer::getProfiles();
        if (method_exists(AnalysisProfile::class, 'isActive')) {
            // Filter active Profile only
            $activeProfile = array_filter(Analyzer::getProfiles(), fn($profile) => $profile->isActive());
        }

        // Add Record to Profile and start recording
        return $this->profile->start($record, $activeProfile)->getUID();
    }

    /**
     * Stop recording
     *
     * @param string|null $uid If provided, stop the Record with corresponding UID. Else, stop the last Record in Profile
     * @return bool
     */
    public function stop(?string $uid = null): void
    {
        // Take snapshot
        $snapshot = Analyzer::takeSnapshot();
        // Get $record
        $record = is_null($uid) ? $this->profile->getLatestActiveRecord() : $this->profile->get($uid);
        // Check if $record is null or is stopped
        if (is_null($record) || $record->isStopped()) return;
        // Set pre stop snapshot
        $record->setPreStopSnapshot($snapshot);

        // Stop
        $this->profile->stop($record->getUID());
    }

    /**
     * Get Profile
     *
     * @return IAProfile
     */
    public function getProfile(): IAProfile
    {
        return $this->profile;
    }
}
