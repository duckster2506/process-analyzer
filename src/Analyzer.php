<?php

namespace Duckster\Analyzer;

use Duckster\Analyzer\Interfaces\IAProfile;
use Duckster\Analyzer\Structures\AnalysisProfile;

class Analyzer
{
    // ***************************************
    // Properties
    // ***************************************

    /**
     * @var IAProfile[] Analyzer profiles
     */
    private static array $profiles = [];

    /**
     * @var AnalyzerConfig|null Config object
     */
    private static ?AnalyzerConfig $config = null;

    // ***************************************
    // Public API
    // ***************************************

    /**
     * Try to init Analyzer
     *
     * @param AnalyzerConfig|null $config
     * @return void
     */
    public static function tryToInit(AnalyzerConfig $config = null): void
    {
        if (isset($config)) {
            self::$config = $config;
        } else if (is_null(self::$config)) {
            self::$config = new AnalyzerConfig();
        }
    }

    /**
     * Get config
     *
     * @return AnalyzerConfig
     */
    public static function config(): AnalyzerConfig
    {
        return self::$config;
    }

    /**
     * Generate a snapshot
     *
     * @param bool $beforeCreate
     * @return array
     */
    public static function takeSnapshot(bool $beforeCreate = true): array
    {
        $mem = memory_get_usage();
        $output = [
            'mem' => $mem,
            'time' => hrtime(true)
        ];

        // Get memory at the point before create output
        if ($beforeCreate) return $output;
        // Get memory at the point after create output
        $output['mem'] = memory_get_usage();

        return $output;
    }

    /**
     * Get Profiles
     *
     * @return IAProfile[]
     */
    public static function getProfiles(): array
    {
        return self::$profiles;
    }

    /**
     * Get or create a Profile by name
     *
     * @param string $name
     * @return AnalyzerEntry|null Return null if disabled
     */
    public static function profile(string $name): ?AnalyzerEntry
    {
        // Take snapshot
        $snapshot = self::takeSnapshot();

        // Check if disabled
        if (!self::$config->enable()) return null;
        // Try to init
        self::tryToInit();

        // Check if Profile is existing
        if (!self::hasProfile($name)) {
            // Create new Profile
            self::$profiles[$name] = AnalysisProfile::create($name);
        }

        return new AnalyzerEntry($snapshot, self::$profiles[$name]);
    }

    /**
     * Add a Profile. Return true if added successfully, else return false
     *
     * @param IAProfile $profile
     * @return bool
     */
    public static function addProfile(IAProfile $profile): bool
    {
        // Check if Profile exists
        if (self::hasProfile($profile->getName())) {
            return false;
        }

        // Create Profile
        self::$profiles[$profile->getName()] = $profile;

        return true;
    }

    /**
     * Delete Profile. Return unprepared Profile if delete successfully, else return null
     *
     * @param string $name
     * @return IAProfile|null
     */
    public static function popProfile(string $name): ?IAProfile
    {
        $output = null;

        if (self::hasProfile($name)) {
            // Get reference
            $output = self::$profiles[$name];
            // Delete
            unset(self::$profiles[$name]);
        }

        return $output;
    }

    /**
     * Clear all Profile
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$profiles = [];
    }

    /**
     * Start recording using Default Profile and return execution UID
     *
     * @param string|null $title
     * @return string
     */
    public static function start(?string $title = null): string
    {
        return self::startProfile(self::$config->defaultProfile(), $title);
    }

    /**
     * Start recoding using $profile Profile and return execution UID
     *
     * @param string $profileName
     * @param string|null $title
     * @return string|null Return null if disabled
     */
    public static function startProfile(string $profileName, ?string $title = null): ?string
    {
        // Start recording
        return self::profile($profileName)?->start($title);
    }

    /**
     * Stop the Record with $executionUID of Default Profile
     *
     * @param string $executionUID
     * @return void
     */
    public static function stop(string $executionUID): void
    {
        self::stopProfile(self::$config->defaultProfile(), $executionUID);
    }

    /**
     * Stop the Record with $executionUID of $profile Profile
     *
     * @param string $profileName
     * @param string $executionUID
     * @return void
     */
    public static function stopProfile(string $profileName, string $executionUID): void
    {
        // Stop recording
        self::profile($profileName)?->stop($executionUID);
    }

    /**
     * Check if Analyzer has $profile Profile
     *
     * @param string $profile
     * @return bool
     */
    public static function hasProfile(string $profile): bool
    {
        return array_key_exists($profile, self::$profiles);
    }

    /**
     * Flush Profile
     *
     * @param string|null $profileName
     * @return void
     */
    public static function flush(?string $profileName = null): void
    {
        // Check if disabled
        if (!self::$config->enable()) return;
        // Create a Printer instance
        $printerInstance = new (self::$config->printer());

        if (is_null($profileName)) {
            // Iterate and flush all Profile
            foreach (array_keys(self::$profiles) as $profileName) {
                $printerInstance->printProfile(self::popProfile($profileName));
            }
            // Clear all Profile
            self::$profiles = [];
        } elseif (self::hasProfile($profileName)) {
            // Pop and print
            $printerInstance->printProfile(self::popProfile($profileName));
        }

    }

    /**
     * Get title (or name) for Record
     *
     * @param string|null $title
     * @return string
     */
    public static function getTitle(?string $title): string
    {
        // Indicate if $title is null
        if (is_null($title)) {
            // Config default
            $default = self::$config->defaultRecordGetter();
            // Indicate if
            if (is_null($default)) {
                // Get the backtrace
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

                return count($backtrace) === 2
                    ? "Function: " . $backtrace[1]['function']
                    : $backtrace[0]['file'] . ":" . ($backtrace[0]['line'] ?? 0);
            } else {
                return $default;
            }
        }

        return $title;
    }
}
