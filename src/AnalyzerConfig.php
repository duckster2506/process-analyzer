<?php

namespace Duckster\Analyzer;

use Duckster\Analyzer\Interfaces\IAProfile;
use Duckster\Analyzer\Interfaces\IARecord;

class AnalyzerConfig
{
    // ***************************************
    // Analyzer
    // ***************************************

    /**
     * @var bool Enable Analyzer
     */
    protected bool $enable = true;

    /**
     * @var string Default Profile name
     */
    protected string $defaultProfile = "Default";

    /**
     * @var callable|array|null Default record name getter
     */
    protected mixed $defaultRecordGetter = null;

    /**
     * Profile's extra data
     *
     * @var array
     */
    protected array $profileExtras = [
        "Default" => [
            "peak" => [
                "handler" => "memory_get_peak_usage",
                "formatter" => [Utils::class, "appendB"],
                "start" => true,
                "stop" => true,
                "diff" => true
            ]
        ]
    ];

    // ***************************************
    // Printer
    // ***************************************

    /**
     * @var string Printer class
     */
    protected string $printer = AnalysisPrinter::class;

    /**
     * @var bool Print Profile as table
     */
    protected bool $prettyPrint = true;

    /**
     * @var bool Print string in one line
     */
    protected bool $oneLine = false;

    /**
     * @var bool Show UID
     */
    protected bool $showUid = true;

    /**
     * @var string|false Print to file
     */
    protected string|false $useFile = "logs/log.txt";

    /**
     * @var bool Print to console
     */
    protected bool $useConsole = true;

    // ***************************************
    // Modify Profile and Record
    // ***************************************

    /**
     * @var string Profile prefix
     */
    protected string $profilePrefix = "";

    /**
     * @var string Profile suffix
     */
    protected string $profileSuffix = "";

    /**
     * @var string Record prefix
     */
    protected string $recordPrefix = "";

    /**
     * @var string Record suffix
     */
    protected string $recordSuffix = "";

    /**
     * @var string Unit of time
     */
    protected string $timeUnit = "ms";

    /**
     * @var mixed Time value formatter
     */
    protected mixed $timeFormatter = null;

    /**
     * @var string Unit of memory
     */
    protected string $memUnit = "KB";

    /**
     * @var mixed Memory value formatter
     */
    protected mixed $memFormatter = null;

    // ***************************************
    // Style
    // ***************************************

    protected string $topLeftChar = "╭";

    protected string $topRightChar = "╮";

    protected string $bottomLeftChar = "╰";

    protected string $bottomRightChar = "╯";

    protected string $topForkChar = "┬";

    protected string $rightForkChar = "┤";

    protected string $bottomForkChar = "┴";

    protected string $leftForkChar = "├";

    protected string $crossChar = "┼";

    protected string $horizontalLineChar = "─";

    protected string $verticalLineChar = "│";

    // ***************************************
    // Overridable
    // ***************************************

    /**
     * Get enable Analyzer
     *
     * @return bool
     */
    public function enable(): bool
    {
        return $this->enable;
    }

    /**
     * Get default Profile name
     *
     * @return string
     */
    public function defaultProfile(): string
    {
        return $this->defaultProfile;
    }

    /**
     * Get default Record name
     *
     * @return string|null
     */
    public function defaultRecordGetter(): ?string
    {
        if (is_callable($this->defaultRecordGetter) || is_array($this->defaultRecordGetter))
            return call_user_func($this->defaultRecordGetter);

        return null;
    }

    /**
     * Profile's extra data
     *
     * @return array
     */
    public function profileExtras(): array
    {
        return $this->profileExtras;
    }

    /**
     * Get printer
     *
     * @return string
     */
    public function printer(): string
    {
        return $this->printer;
    }

    /**
     * Print as table
     *
     * @return bool
     */
    public function prettyPrint(): bool
    {
        return $this->prettyPrint;
    }

    /**
     * Print string in one line
     *
     * @return bool
     */
    public function oneLine(): bool
    {
        return $this->oneLine;
    }

    /**
     * Show UID
     *
     * @return bool
     */
    public function showUID(): bool
    {
        return $this->showUid;
    }

    /**
     * Print to file
     *
     * @return string|false
     */
    public function useFile(): string|false
    {
        return $this->useFile;
    }

    /**
     * Print to console
     *
     * @return bool
     */
    public function useConsole(): bool
    {
        return $this->useConsole;
    }

    /**
     * Profile prefix
     *
     * @return string
     */
    public function profilePrefix(): string
    {
        return $this->profilePrefix;
    }

    /**
     * Profile prefix
     *
     * @return string
     */
    public function profileSuffix(): string
    {
        return $this->profileSuffix;
    }

    /**
     * Profile prefix
     *
     * @return string
     */
    public function recordPrefix(): string
    {
        return $this->recordPrefix;
    }

    /**
     * Profile prefix
     *
     * @return string
     */
    public function recordSuffix(): string
    {
        return $this->recordSuffix;
    }

    /**
     * Get unit of time
     *
     * @return string
     */
    public function timeUnit(): string
    {
        return $this->timeUnit;
    }

    /**
     * Time value formatter
     *
     * @param float $value
     * @return mixed
     */
    public function timeFormatter(float $value): string
    {
        if (is_callable($this->timeFormatter) || is_array($this->timeFormatter))
            return call_user_func($this->timeFormatter, $value);

        $offset = match (strtolower($this->timeUnit)) {
            "ns", "nanosecond" => 1,
            "μs", "microsecond" => 1e+3,
            "s", "second" => 1e+9,
            default => 1e+6
        };

        $unit = match (strtolower($this->timeUnit)) {
            "ns", "nanosecond" => "ns",
            "μs", "microsecond" => "μs",
            "s", "second" => "s",
            default => "ms"
        };

        return round($value / $offset, 3) . " $unit";
    }

    /**
     * Get unit of memory
     *
     * @return string
     */
    public function memUnit(): string
    {
        return $this->memUnit;
    }

    /**
     * Memory value formatter
     *
     * @return mixed
     */
    public function memFormatter(int $value): string
    {
        if (is_callable($this->memFormatter) || is_array($this->memFormatter))
            return call_user_func($this->memFormatter, $value);

        $offset = match (strtolower($this->memUnit)) {
            "b", "byte" => 1,
            "kb", "kilobyte" => 1024,
            "gb", "gigabyte" => 1073741824,
            default => 1048576
        };

        $unit = match (strtolower($this->memUnit)) {
            "b", "byte" => "b",
            "kb", "kilobyte" => "kb",
            "gb", "gigabyte" => "gb",
            default => "mb"
        };

        return round($value / $offset, 3) . " " . strtoupper($unit);
    }

    /**
     * Get top left corner character
     *
     * @return string
     */
    public function topLeftChar(): string
    {
        return $this->topLeftChar;
    }

    /**
     * Get top right corner character
     *
     * @return string
     */
    public function topRightChar(): string
    {
        return $this->topRightChar;
    }

    /**
     * Get bottom left corner character
     *
     * @return string
     */
    public function bottomLeftChar(): string
    {
        return $this->bottomLeftChar;
    }

    /**
     * Get bottom right corner character
     *
     * @return string
     */
    public function bottomRightChar(): string
    {
        return $this->bottomRightChar;
    }

    /**
     * Get top fork character
     *
     * @return string
     */
    public function topForkChar(): string
    {
        return $this->topForkChar;
    }

    /**
     * Get right fork character
     *
     * @return string
     */
    public function rightForkChar(): string
    {
        return $this->rightForkChar;
    }

    /**
     * Get bottom fork character
     *
     * @return string
     */
    public function bottomForkChar(): string
    {
        return $this->bottomForkChar;
    }

    /**
     * Get left fork character
     *
     * @return string
     */
    public function leftForkChar(): string
    {
        return $this->leftForkChar;
    }

    /**
     * Get cross character
     *
     * @return string
     */
    public function crossChar(): string
    {
        return $this->crossChar;
    }

    /**
     * Get horizontal line character
     *
     * @return string
     */
    public function horizontalLineChar(): string
    {
        return $this->horizontalLineChar;
    }

    /**
     * Get vertical line character
     *
     * @return string
     */
    public function verticalLineChar(): string
    {
        return $this->verticalLineChar;
    }

    // ***************************************
    // Printer's hooks
    // ***************************************

    /**
     * Before modifying Profile
     *
     * @param IAProfile $profile
     * @return void
     */
    public function onPreprocessProfile(IAProfile $profile): void
    {
    }

    /**
     * Before modifying Record
     *
     * @param IARecord $record
     * @return void
     */
    public function onPreprocessRecord(IARecord $record): void
    {
    }

    /**
     * After converting Record to String
     * @param string $content
     * @return void
     */
    public function onEachRecordString(string $content): void
    {
    }

    /**
     * Before print Profile's statistic report
     *
     * @param string $content
     * @return void
     */
    public function onPrintProfileString(string $content): void
    {
    }
}
