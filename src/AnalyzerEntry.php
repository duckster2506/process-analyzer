<?php

namespace Duckstery\Analyzer;

use Duckstery\Analyzer\Interfaces\IAProfile;

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

    /**
     * Constructor
     *
     * @param IAProfile $profile
     */
    public function __construct(IAProfile $profile)
    {
        $this->profile = $profile;
        $this->snapshot = [];
    }

    /**
     * Prepare Entry
     *
     * @param array $snapshot
     * @return AnalyzerEntry
     */
    public function prepare(array $snapshot): AnalyzerEntry
    {
        $this->snapshot = $snapshot;

        return $this;
    }

    /**
     * Start recording
     *
     * @param string|null $title
     * @return string
     */
    public function start(?string $title = null): string
    {
        // Get Record class
        $recordClass = Analyzer::config()->record();
        // Check if Record class has "open" method
        if (!method_exists($recordClass, "open")) return "";

        // Create a Record and set pre snapshot
        $record = $recordClass::open(Analyzer::getCallerAsDefault($title))->setPreStartSnapshot($this->snapshot);

        // Add Record to Profile and start recording
        return $this->profile->start($record, Analyzer::getProfiles(), Analyzer::getExtras($this->profile))->getUID();
    }

    /**
     * Stop recording
     *
     * @param string|null $uid If provided, stop the Record with corresponding UID. Else, stop the last Record in Profile
     * @return void
     */
    public function stop(?string $uid = null): void
    {
        // Get $record
        $record = is_null($uid) ? $this->profile->getLatestActiveRecord() : $this->profile->get($uid);
        // Check if $record is null or is stopped
        if (is_null($record) || $record->isStopped()) return;
        // Set pre stop snapshot
        $record->setPreStopSnapshot($this->snapshot);

        // Stop
        $this->profile->stop($record->getUID(), Analyzer::getExtras($this->profile));
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
