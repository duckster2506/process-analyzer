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
        $record = $recordClass::open(self::getCallerAsDefault($title))->setPreStartSnapshot($this->snapshot);

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

    /**
     * Get title (or name) for Record
     *
     * @param string|null $title
     * @return string
     */
    public static function getCallerAsDefault(?string $title): string
    {
        // Indicate if $title is null
        if (is_null($title)) {
            // Config default
            $default = Analyzer::config()->defaultRecordGetter();
            // Indicate if
            if (is_null($default)) {
                // Get the backtrace
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
                file_put_contents("logs/log.txt", print_r($backtrace, true), FILE_APPEND);
                // Count the actual size of $backtrace
                $size = count($backtrace);
                // Get output
                $output = $backtrace[$size - 1]['file'] . ":" . ($backtrace[$size - 1]['line'] ?? 0);

                if ($size === 4) {
                    // Get index of caller
                    $index = 2;
                    // Check if called by Analyzer::start or Analyzer::startProfile
                    if ($backtrace[2]['class'] === Analyzer::class) {
                        $index = 3;
                    }

                    return "Function: " . $backtrace[$index]['function'];
                }
                return $output;
            } else {
                return $default;
            }
        }

        return $title;
    }
}
