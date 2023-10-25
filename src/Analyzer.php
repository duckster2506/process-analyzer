<?php

namespace Duckster\Analyzer;

use Duckster\Analyzer\Interfaces\IARecord;
use Duckster\Analyzer\Structures\AnalysisProfile;
use Duckster\Analyzer\Structures\AnalysisRecord;
use Exception;
use phpDocumentor\Reflection\Types\Self_;

class Analyzer
{
    // ***************************************
    // Configurable
    // ***************************************

    /**
     * @var string Default Profile name
     */
    protected static string $defaultProfile = "Default";

    /**
     * @var string[] Default record name getter
     */
    protected static ?array $defaultRecordGetter = null;

    /**
     * @var bool Print Profile as ASCII table
     */
    protected static bool $prettyPrint = true;

    /**
     * @var string Printer class
     */
    protected static string $printer = AnalysisPrinter::class;

    // ***************************************
    // Properties
    // ***************************************

    /**
     * @var AnalysisProfile[] Analyzer profiles
     */
    private static array $profiles = [];

    // ***************************************
    // Public API
    // ***************************************

    /**
     * Generate a snapshot
     *
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
     * @return AnalysisProfile[]
     */
    public static function getProfiles(): array
    {
        return self::$profiles;
    }

    /**
     * Get a prepared Profile by name
     *
     * @param string $name
     * @param bool $prepare If true, create Profile if not exist and return a prepared Profile. Else, throw Exception
     * @return AnalysisProfile
     * @throws Exception
     */
    public static function profile(string $name, bool $prepare = true): AnalysisProfile
    {
        // Take snapshot
        $snapshot = self::takeSnapshot();

        // Check if Profile is existing
        if (!self::hasProfile($name)) {
            // Check if Exception should be thrown
            if (!$prepare) throw new Exception("Profile not found");
            // Create Profile
            self::$profiles[$name] = AnalysisProfile::create($name);
        }

        if ($prepare) {
            return self::$profiles[$name]?->prep($snapshot);
        }

        return self::$profiles[$name];
    }

    /**
     * Add a Profile. Return true if added successfully, else return false
     *
     * @param AnalysisProfile $profile
     * @return bool
     */
    public static function addProfile(AnalysisProfile $profile): bool
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
     * @return AnalysisProfile|null
     */
    public static function popProfile(string $name): ?AnalysisProfile
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
        return self::startProfile(static::$defaultProfile ?? "Default", $title);
    }

    /**
     * Start recoding using $profile Profile and return execution UID
     *
     * @param string $profileName
     * @param string|null $title
     * @return string
     */
    public static function startProfile(string $profileName, ?string $title = null): string
    {
        // Start recording
        return self::profile($profileName)->write(static::getTitle($title));
    }

    /**
     * Start recording using multiple Profile. Throw Exception if Profile(s) not exist
     *
     * @param array $profileNames
     * @param string|null $title
     * @return IARecord
     */
    public static function startShared(array $profileNames, ?string $title = null): IARecord
    {
        // Take a snapshot
        $snapshot = self::takeSnapshot();
        // Create a shared Record
        $record = AnalysisRecord::open(static::getTitle($title), true);

        // Iterate through each Profile and put $record
        foreach ($profileNames as $profileName) {
            self::profile($profileName)
                ->prep($snapshot)
                ->put($record);
        }

        // Start $record
        $record->start();

        return $record;
    }

    /**
     * Stop the Record with $executionUID of Default Profile
     *
     * @param string $executionUID
     * @return void
     */
    public static function stop(string $executionUID): void
    {
        self::stopProfile(static::$defaultProfile ?? "Default", $executionUID);
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
        self::profile($profileName, false)
            ?->stop($executionUID)
            ?->setPostSnapshot(self::takeSnapshot(false));
    }

    /**
     * Stop a shared Record
     *
     * @param IARecord $record
     * @return void
     */
    public static function stopShared(IARecord $record): void
    {
        $record->stop()->setPostSnapshot(self::takeSnapshot(false));
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
        // Create a Printer instance
        $printerInstance = new self::$printer;

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

    // ***************************************
    // Overridable
    // ***************************************

    /**
     * Get title (or name) for Record
     *
     * @return string
     */
    protected static function getTitle(?string $title): string
    {
        // Indicate if $title is null
        if (is_null($title)) {
            // Indicate if
            if (is_null(static::$defaultRecordGetter)) {
                // Get the backtrace
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

                return count($backtrace) === 2
                    ? "Function: " . $backtrace[1]['function']
                    : $backtrace[0]['file'] . ":" . ($backtrace[0]['line'] ?? 0);
            } else {
                return call_user_func(static::$defaultRecordGetter);
            }
        }

        return $title;
    }
}
