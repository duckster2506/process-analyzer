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
     * @var AnalyzerConfig|null Config object
     */
    private static ?AnalyzerConfig $config = null;

    /**
     * @var IAProfile[] Analyzer profiles
     */
    private static array $profiles = [];

    /**
     * @var AnalyzerEntry[] Analyzer entries
     */
    private static array $entries = [];

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
        if (!is_null($config)) {
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
        if (is_null(self::$config)) self::tryToInit();
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
        $time = hrtime(true);
        $mem = memory_get_usage();
        $output = [
            'mem' => $mem,
            'time' => $time
        ];

        // Get memory at the point before create output
        if ($beforeCreate) return $output;
        // Get memory at the point after create output
        $output['mem'] = memory_get_usage();
        $output['time'] = hrtime(true);

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
     * Get an entry of a Profile
     *
     * @param string $name
     * @return AnalyzerEntry|null Return null if disabled
     */
    public static function profile(string $name): ?AnalyzerEntry
    {
        // Take snapshot
        $snapshot = self::takeSnapshot();

        // Try to init
        self::tryToInit();
        // Check if disabled
        if (!self::$config->enable()) return null;

        // Check if Profile is existing
        if (!self::hasProfile($name)) {
            // Create new Profile
            self::$profiles[$name] = AnalysisProfile::create($name);
            // Create new AnalysisEntry
            self::$entries[$name] = new AnalyzerEntry(self::$profiles[$name]);
        }

        return self::$entries[$name]->prepare($snapshot);
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
        // Create Entry
        self::$entries[$profile->getName()] = new AnalyzerEntry($profile);

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
            // Delete $profile
            unset(self::$profiles[$name]);
            // Delete $entry
            unset(self::$entries[$name]);
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
        self::$entries = [];
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
        return self::profile($profileName)?->start(self::getCallerAsDefault($title));
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
        } elseif (self::hasProfile($profileName)) {
            // Pop and print
            $printerInstance->printProfile(self::popProfile($profileName));
        }
    }

    /**
     * Get Profile's extras
     *
     * @param IAProfile $profile
     * @return array
     */
    public static function getExtras(IAProfile $profile): array
    {
        return self::$config->profileExtras()[$profile->getName()] ?? [];
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
            $default = self::$config->defaultRecordGetter();
            // Indicate if
            if (is_null($default)) {
                // Get the backtrace
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

                return count($backtrace) === 3
                    ? "Function: " . $backtrace[2]['function']
                    : $backtrace[1]['file'] . ":" . ($backtrace[1]['line'] ?? 0);
            } else {
                return $default;
            }
        }

        return $title;
    }
}
