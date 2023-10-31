<?php

namespace Duckster\Analyzer;

use Duckster\Analyzer\Interfaces\IAPrinter;
use Duckster\Analyzer\Interfaces\IAProfile;
use Duckster\Analyzer\Interfaces\IARecord;
use Duckster\Analyzer\Structures\AnalysisDataset;
use Duckster\Analyzer\Structures\AnalysisProfile;

class AnalysisPrinter extends IAPrinter
{
    // ***************************************
    // Properties
    // ***************************************

    /**
     * @var string[] Preprocessed Record's data
     */
    private array $data;

    /**
     * @var AnalysisDataset[] Pretty print datasets
     */
    private array $datasets;

    /**
     * @var int Count the number of Record
     */
    private int $count;

    /**
     * @var string Final content
     */
    private string $content;

    // ***************************************
    // Public API
    // ***************************************

    public function __construct()
    {
        $this->content = "";
        $this->count = 0;
    }

    /**
     * Print Profile
     *
     * @param AnalysisProfile $profile
     * @return void
     */
    public function printProfile(IAProfile $profile): void
    {
        // Hook before convert Profile
        $this->preprocessProfile($profile);

        // Iterate through each $record
        foreach ($profile->getRecords() as $record) {
            // Increase count
            $this->count += 1;
            // Preprocess Record's data
            $this->data = $this->preprocessRecord($record);

            // Check if Printer should prepare for pretty print
            if (Analyzer::config()->getPrettyPrint()) {
                // Convert Record to datasets
                $this->datasets = $this->convertToAndPushToDatasets($this->data, $this->datasets ?? []);
            } else {
                // Convert to printable cols
                $this->content .= $this->convertToString($this->data);
            }
        }

        // Check if pretty print
        if (Analyzer::config()->getPrettyPrint()) {
            // Convert to table
            $this->content = $this->convertToTable($this->datasets);
        }

        $this->printContent($this->content);
    }

    // ***************************************
    // Private API
    // ***************************************

    /**
     * Preprocess Profile
     *
     * @param IAProfile $profile
     * @return void
     */
    public function preprocessProfile(IAProfile $profile): void
    {
        // Call hook
        Utils::callHook(Analyzer::config(), "onPreprocessProfile", $profile);
        // Apply prefix and suffix for Profile's name
        $profile->setName(
            Analyzer::config()->getProfilePrefix() . $profile->getName() . Analyzer::config()->getProfileSuffix()
        );
    }

    /**
     * Preprocess Record
     *
     * @param IARecord $record
     * @return array
     */
    public function preprocessRecord(IARecord $record): array
    {
        // Hook: beforePrint for AnalysisProfile
        Utils::callHook(Analyzer::config(), "onPreprocessRecord", $record);

        // $record new Name
        $name = Analyzer::config()->getRecordPrefix() . $record->getName() . Analyzer::config()->getRecordSuffix();
        // Apply prefix and suffix for Profile's name
        $record->setName($name);

        // Preprocess other data
        return [
            'uid' => $record->getUID(),
            'name' => $record->getName(),
            'time' => Utils::applyFormatter($record->diffTime(), Analyzer::config()->getTimeFormatter()),
            'memory' => Utils::applyFormatter($record->diffMem(), Analyzer::config()->getMemFormatter())
        ];
    }

    /**
     * Convert Record to datasets
     *
     * @param array $data
     * @param array $datasets
     * @return AnalysisDataset[]
     */
    public function convertToAndPushToDatasets(array $data, array $datasets): array
    {
        // Iterate through each $data key
        foreach ($data as $key => $value) {
            // Check if dataset is set
            if (!isset($datasets[$key])) $datasets[$key] = new AnalysisDataset(strlen($key));
            // Add data to dataset
            $datasets[$key]->add($value);
        }

        return $datasets;
    }

    /**
     * Convert Record to string
     *
     * @param array $data
     * @return string
     */
    public function convertToString(array $data): string
    {
        $line = [""];
        // UID
        if (Analyzer::config()->getShowUID()) $line[0] = sprintf("[%s] ", $data['uid']);
        // Name
        $line[0] .= sprintf("%s:", $data['name']);
        // Execution time
        $line[] = sprintf("Time: %s", $data['time']);
        // Execution memory
        $line[] = sprintf("Memory: %s", $data['memory']);

        // Create content
        $content = implode(Analyzer::config()->getOneLine() ? " " : (PHP_EOL . "\t"), $line);
        // Call hook
        Utils::callHook(Analyzer::config(), "onEachRecordString", $content);

        // Add to content
        return $content . PHP_EOL;
    }

    /**
     * Convert datasets to table string
     *
     * @param AnalysisDataset[] $datasets
     * @return string
     */
    public function convertToTable(array $datasets): string
    {
        // Create column width structure (2 for padding)
        $widthOfColumns = array_map(fn($dataset) => $dataset->getMaxLength() + 2, $datasets);

        $output = $this->createHeader($widthOfColumns);

        // Iterate through each
        for ($i = 0; $i < $this->count; $i++) {
            // Iterate through each column
            foreach ($widthOfColumns as $header => $width) {
                $output .= Analyzer::config()->getVerticalLineChar()
                    . str_pad(" " . $datasets[$header]->get($i) . " ", $width);
            }

            // Add last border and linebreak
            $output .= Analyzer::config()->getVerticalLineChar() . PHP_EOL;
        }

        // Create last border
        $output .= $this->createBorderRow(
            $widthOfColumns,
            Analyzer::config()->getBottomLeftChar(),
            Analyzer::config()->getBottomForkChar(),
            Analyzer::config()->getBottomRightChar()
        );

        return $output;
    }

    /**
     * Print content
     *
     * @return void
     */
    public function printContent(string $content)
    {
        // Hook: printRecord
        Utils::callHook(Analyzer::config(), "onPrintProfileString", $this->content);

        // Check if Printer should print to file
        $useFile = Analyzer::config()->getUseFile();
        if (!!$useFile) {
            // Get file name
            file_put_contents($useFile === true ? "logs/log.txt" : $useFile, $content . PHP_EOL, FILE_APPEND);
        }

        // Check if Printer should print to console
        if (Analyzer::config()->getUseConsole()) {
            // Print to console
            printf("%s", $content . PHP_EOL);
        }
    }

    /**
     * Create border row (row with no content)
     *
     * @param array $widthOfColumns
     * @param string $start
     * @param string $separator
     * @param string $end
     * @return string
     */
    public function createBorderRow(array $widthOfColumns, string $start, string $separator, string $end): string
    {
        $content = array_map(fn($width) => str_repeat(Analyzer::config()->getHorizontalLineChar(), $width), $widthOfColumns);
        return $start . implode($separator, $content) . $end;
    }

    /**
     * Create header
     *
     * @param array $widthOfColumns
     * @return string
     */
    public function createHeader(array $widthOfColumns): string
    {
        // Create first row (top border)
        $output = $this->createBorderRow(
                $widthOfColumns,
                Analyzer::config()->getTopLeftChar(),
                Analyzer::config()->getTopForkChar(),
                Analyzer::config()->getTopRightChar()
            ) . PHP_EOL;

        // Iterate through each dataset's keys to create header
        foreach (array_keys($this->datasets) as $key) {
            // Add headers
            $output .= Analyzer::config()->getVerticalLineChar()
                . str_pad(" " . ucfirst($key) . " ", $widthOfColumns[$key]);
        }

        // Create border to separate header and content
        $output .= Analyzer::config()->getVerticalLineChar() . PHP_EOL . $this->createBorderRow(
                $widthOfColumns,
                Analyzer::config()->getLeftForkChar(),
                Analyzer::config()->getCrossChar(),
                Analyzer::config()->getRightForkChar()
            );

        return $output . PHP_EOL;
    }
}
