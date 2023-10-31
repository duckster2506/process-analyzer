<?php

namespace Duckster\Analyzer;

class AnalyzerConfig
{
    // ***************************************
    // Analyzer
    // ***************************************

    /**
     * @var string Default Profile name
     */
    protected string $defaultProfile = "Default";

    /**
     * @var callable|array|null Default record name getter
     */
    protected mixed $defaultRecordGetter = null;

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
    protected bool $oneLine = true;

    /**
     * @var bool Show UID
     */
    protected bool $showUid = true;

    /**
     * @var string|bool Print to file
     */
    protected string|bool $useFile = "logs/log.txt";

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
     * @var mixed Diff time formatter
     */
    protected mixed $timeFormatter = null;

    /**
     * @var string Unit of memory
     */
    protected string $memUnit = "KB";

    /**
     * @var mixed Diff memory formatter
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
     * @return string
     */
    public function getDefaultProfile(): string
    {
        return $this->defaultProfile;
    }

    /**
     * @return mixed
     */
    public function getDefaultRecordGetter(): mixed
    {
        return $this->defaultRecordGetter;
    }

    /**
     * Get printer
     *
     * @return string
     */
    public function getPrinter(): string
    {
        return $this->printer;
    }

    /**
     * Print as table
     *
     * @return bool
     */
    public function getPrettyPrint(): bool
    {
        return $this->prettyPrint;
    }

    /**
     * Print string in one line
     *
     * @return bool
     */
    public function getOneLine(): bool
    {
        return $this->oneLine;
    }

    /**
     * Show UID
     *
     * @return bool
     */
    public function getShowUID(): bool
    {
        return $this->showUid;
    }

    /**
     * Print to file
     *
     * @return bool|string
     */
    public function getUseFile(): bool|string
    {
        return $this->useFile;
    }

    /**
     * Print to console
     *
     * @return bool
     */
    public function getUseConsole(): bool
    {
        return $this->useConsole;
    }


    /**
     * Profile prefix
     *
     * @return string
     */
    public function getProfilePrefix(): string
    {
        return $this->profilePrefix;
    }

    /**
     * Profile prefix
     *
     * @return string
     */
    public function getProfileSuffix(): string
    {
        return $this->profileSuffix;
    }

    /**
     * Profile prefix
     *
     * @return string
     */
    public function getRecordPrefix(): string
    {
        return $this->recordPrefix;
    }

    /**
     * Profile prefix
     *
     * @return string
     */
    public function getRecordSuffix(): string
    {
        return $this->recordSuffix;
    }

    /**
     * Get unit of time
     *
     * @return string
     */
    public function getTimeUnit(): string
    {
        return $this->timeUnit;
    }

    /**
     * Diff time formatter
     *
     * @return mixed
     */
    public function getTimeFormatter(): mixed
    {
        return $this->timeFormatter;
    }

    /**
     * Get unit of memory
     *
     * @return string
     */
    public function getMemUnit(): string
    {
        return $this->memUnit;
    }

    /**
     * Diff mem formatter
     *
     * @return mixed
     */
    public function getMemFormatter(): mixed
    {
        return $this->memFormatter;
    }

    /**
     * Get top left corner character
     *
     * @return string
     */
    public function getTopLeftChar(): string
    {
        return $this->topLeftChar;
    }

    /**
     * Get top right corner character
     *
     * @return string
     */
    public function getTopRightChar(): string
    {
        return $this->topRightChar;
    }

    /**
     * Get bottom left corner character
     *
     * @return string
     */
    public function getBottomLeftChar(): string
    {
        return $this->bottomLeftChar;
    }

    /**
     * Get bottom right corner character
     *
     * @return string
     */
    public function getBottomRightChar(): string
    {
        return $this->bottomRightChar;
    }

    /**
     * Get top fork character
     *
     * @return string
     */
    public function getTopForkChar(): string
    {
        return $this->topForkChar;
    }

    /**
     * Get right fork character
     *
     * @return string
     */
    public function getRightForkChar(): string
    {
        return $this->rightForkChar;
    }

    /**
     * Get bottom fork character
     *
     * @return string
     */
    public function getBottomForkChar(): string
    {
        return $this->bottomForkChar;
    }

    /**
     * Get left fork character
     *
     * @return string
     */
    public function getLeftForkChar(): string
    {
        return $this->leftForkChar;
    }

    /**
     * Get cross character
     *
     * @return string
     */
    public function getCrossChar(): string
    {
        return $this->crossChar;
    }

    /**
     * Get horizontal line character
     *
     * @return string
     */
    public function getHorizontalLineChar(): string
    {
        return $this->horizontalLineChar;
    }

    /**
     * Get vertical line character
     *
     * @return string
     */
    public function getVerticalLineChar(): string
    {
        return $this->verticalLineChar;
    }
}
