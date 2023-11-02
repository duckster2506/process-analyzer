<?php

namespace Duckster\Analyzer\Interfaces;

interface IAPrinter
{
    public function printProfile(IAProfile $profile): void;
}
