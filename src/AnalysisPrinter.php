<?php

namespace Duckster\Analyzer;

use Duckster\Analyzer\Interfaces\Printer;
use Duckster\Analyzer\Structures\AnalysisProfile;
use Duckster\Analyzer\Structures\AnalysisRecord;

class AnalysisPrinter implements Printer
{
    // ***************************************
    // Configurable
    // ***************************************

    protected bool $hook = false;

    // ***************************************
    // Public API
    // ***************************************

    public function __construct()
    {
    }

    /**
     * Print Profile
     *
     * @param AnalysisProfile $profile
     * @return void
     */
    public function printProfile(AnalysisProfile $profile): void
    {
        // Hook before convert
        $this->onBeforeConvert($profile);

        
    }
//┌──────────┬────────────────────┐
//│Zend      │Framework           │
//│──────────│────────────────────│
//│Zend      │Framework           │
//└──────────┴────────────────────┘
    // ***************************************
    // Private API
    // ***************************************

    /**
     * Before convert to string
     *
     * @param $profile
     * @return void
     */
    private function onBeforeConvert($profile): void
    {
        // Check if allow to hook
        if ($this->hook) {

            if (method_exists($this, "onProfileBeforeConvert")) {
                // Hook: beforePrint for AnalysisProfile
                $this->onProfileBeforeConvert($profile);
            }

            if (method_exists($this, "onRecordBeforeConvert")) {
                // Hook: before
                foreach ($profile->getRecords() as $record) {
                    $this->onRecordBeforeConvert($record);
                }
            }
        }
    }
}