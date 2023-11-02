<?php

namespace Duckster\Analyzer\Structures;

class AnalysisDataset
{
    // ***************************************
    // Properties
    // ***************************************

    /**
     * @var int Max length of data
     */
    private int $maxLength;

    /**
     * @var string[] Data
     */
    private array $data;

    // ***************************************
    // Public API
    // ***************************************

    /**
     * Constructor
     *
     * @param int $maxLength
     */
    public function __construct(int $maxLength)
    {
        $this->maxLength = $maxLength;
        $this->data = [];
    }

    /**
     * Add data
     *
     * @param string $dataPiece
     * @return void
     */
    public function add(string $dataPiece): void
    {
        // Get $data length
        $length = strlen($dataPiece);
        // Check if $length is higher than $maxLength
        if ($length > $this->maxLength) {
            $this->maxLength = $length;
        }

        // Add $data
        $this->data[] = $dataPiece;
    }

    /**
     * Get data at index
     *
     * @param int $index
     * @return string
     */
    public function get(int $index): string
    {
        return $this->data[$index];
    }

    /**
     * Get max length
     *
     * @return int
     */
    public function getMaxLength(): int
    {
        return $this->maxLength;
    }
}
