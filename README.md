# Dependency free process analyzer for PHP

<details>
  <summary>Table of Contents</summary>
  <ol>
    <li><a href="#">Feature</a></li>
    <li>
      <a href="#installation">Installation</a>
      <ul>
        <li><a href="#for-php-8">PHP 8+</a></li>
        <li><a href="#for-laravel">Laravel</a></li>
      </ul>
    </li>
    <li>
        <a href="#configuration">Configuration</a>
        <ul>
            <li><a href="#how-to-config">How to config</a></li>
            <li><a href="#options">Options</a>
            <li><a href="#printers-hooks">Printer's hooks</a></li>
        </ul>
    </li>
    <li>
        <a href="#usage">Usage</a>
        <ul>
            <li><a href="#basic">Basic</a></li>
            <li><a href="#use-default-profile-name-and-extra-metrics">Use default Profile name and extra metrics</a>
            <li><a href="#use-default-record-name">Use default Record name</a></li>
            <li><a href="#use-multiple-profile">Use multiple Profile</a></li>
        </ul>
    </li>
    <li><a href="#testing">Testing</a></li>
    <li><a href="#issue">Issue</a></li>
    <li><a href="#license">License</a></li>
  </ol>
</details>

## Feature

* Provide ```Analyzer``` that can measure the amount of time and memory of blocks of code
* Report measured amount through file or console
* Support multiple Profile for multiple metrics

For example, you can do this:

```php
// Start of code
$uid = Analyzer::start("Do a simple math");

// Calculating
$output = 1 + 1;
// Print output to screen
echo $output;

// End of code
Analyzer::stop($uid);
// Flush to get report
Analyzer::flush();
```

After that, you will get a report like this

```txt
Default --------------------
╭───────────────┬──────────────────┬─────────────┬────────┬────────────┬────────────┬───────────╮
│ Uid           │ Name             │ Time        │ Memory │ Start peak │ Stop peak  │ Diff peak │
├───────────────┼──────────────────┼─────────────┼────────┼────────────┼────────────┼───────────┤
│ 654af62889e08 │ Do a simple math │ 2006.472 ms │ 2.5 KB │ 16502872 B │ 16502872 B │       0 B │
╰───────────────┴──────────────────┴─────────────┴────────┴────────────┴────────────┴───────────╯
----------------------------
```

The report can be printed to file or console.
Moreover, you can decide to grab the report result and print it to wherever you want

## Installation

### For PHP 8+

Run script to install

```shell
composer require --dev duckstery/process-analyzer
```

### For Laravel

> [!WARNING]  
> This integration only work properly while handling request individually (1 request at a time) because it'll flush everything out at the end of the request.

Run script to install

```shell
composer require --dev duckstery/laravel-process-analyzer
```

Package's ServiceProvider will be auto required by Laravel.
If you don't use auto-discovery, you need to manually add ServiceProvider

```php
Duckstery\Laravel\Analyzer\ProcessAnalyzerServiceProvider::class
```

to the providers array in ```config/app.php```

```php
/*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */
        Duckstery\Laravel\Analyzer\ProcessAnalyzerServiceProvider::class, // ** //
        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])->toArray(),
```

Then, you can publish config file for better customization

```shell
php artisan vendor:publish --provider="Duckstery\Laravel\Analyzer\ProcessAnalyzerServiceProvider"
```

## Configuration

### How to config

Before use, you should config package to match your needs.

To config, create a class and extend ```AnalyzerConfig``` like this

```php
<?php

use Duckstery\Analyzer\AnalyzerConfig;

class MyAnalyzerConfig extends AnalyzerConfig
{
    // Todo
}
```

Then, you can override ```AnalyzerConfig```'s properties or functions and change their value

```php
<?php

use Duckstery\Analyzer\AnalyzerConfig;

class MyAnalyzerConfig extends AnalyzerConfig
{
    // Property override
    protected bool $prettyPrint;
    
    // Function override
    public function prettyPrint(): bool
    {
        return true;
    }
}
```

Beware that config with function override will ignore the property's value. If you config logic is big, you should use
function override instead.

After creating config class, init ```Analyzer``` with your class's instance

```php
<?php

use Duckstery\Analyzer\Analyzer;

Analyzer::tryToInit(new MyAnalyzerConfig());
```

### Options

```enable```: This option will enable and disable ```Analyzer```. With this option, you can include this package in
production. If you need it to measure your process in production, you can switch it on. But remember to switch it off
after

* Type: &nbsp;&nbsp;&nbsp; ```bool```
* Default: &nbsp; true

```defaultProfile```: Define default Profile name

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "Default"

```defaultRecordGetter```: Define getter method name. This method will return Record's default name

* Type: &nbsp;&nbsp;&nbsp; ```array``` | ```string``` | ```null```
* Default: &nbsp;

```profileExtras```: Define extra metrics for Profile. These metrics can be retrieved at the start or end of execution.
After that, ```Analyzer``` can calculate difference or format them

* Type: &nbsp;&nbsp;&nbsp; ```array```
* Default: &nbsp;

```php
[
    "Default" => [
        "peak" => [
            // Metrics method name's or callback array
            "handler" => "memory_get_peak_usage",
            // Formatter
            "formatter" => [Utils::class, "appendB"],
            // Get at the start
            "start" => true,
            // Get at the end
            "stop" => true,
            // Calculate difference
            "diff" => true
        ]
    ]
]
```

```profile```: Profile class. You can customize by create a new class that extend ```IAProfile```

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp;

```php
Duckstery\Analyzer\Structures\AnalysisProfile::class
```

```record```: Record class

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp;

```php
Duckstery\Analyzer\Structures\AnalysisRecord::class
```

```printer```: Printer class

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp;

```php
Duckstery\Analyzer\AnalysisPrinter::class
```

```prettyPrint```: Print report in table

* Type: &nbsp;&nbsp;&nbsp; ```bool```
* Default: &nbsp; true
* Example:

```php
$prettyPrint = true;
//    Default --------------------
//    ╭───────────────┬──────────────────┬─────────────┬────────┬────────────┬────────────┬───────────╮
//    │ Uid           │ Name             │ Time        │ Memory │ Start peak │ Stop peak  │ Diff peak │
//    ├───────────────┼──────────────────┼─────────────┼────────┼────────────┼────────────┼───────────┤
//    │ 654af62889e08 │ Do a simple math │ 2006.472 ms │ 2.5 KB │ 16502872 B │ 16502872 B │       0 B │
//    ╰───────────────┴──────────────────┴─────────────┴────────┴────────────┴────────────┴───────────╯
//    ----------------------------

$prettyPrint = false;
//    Default --------------------
//    [654af6159585c] Do a simple math:
//        Time ⇒ [2000.605 ms];
//        Memory ⇒ [2.5 KB];
//        Start peak ⇒ [16501872 B];
//        Stop peak ⇒ [16501872 B];
//        Diff peak ⇒ [0 B];
//    ----------------------------
```

```oneLine```: Print each Record in report in a line. Ignored if ```prettyPrint```: true

* Type: &nbsp;&nbsp;&nbsp; ```bool```
* Default: &nbsp;
* Example:

```php
$oneLine = true;
//    Default --------------------
//    [654b2b522090f] Do a simple math: Time ⇒ [2009.288 ms]; Memory ⇒ [2.5 KB]; Start peak ⇒ [16501904 B]; Stop peak ⇒ [16501904 B]; Diff peak ⇒ [0 B];
//    ----------------------------

$oneLine = false;
//    Default --------------------
//    [654af6159585c] Do a simple math:
//        Time ⇒ [2000.605 ms];
//        Memory ⇒ [2.5 KB];
//        Start peak ⇒ [16501872 B];
//        Stop peak ⇒ [16501872 B];
//        Diff peak ⇒ [0 B];
//    ----------------------------
```

```showUID```: Show Record's UID in report

* Type: &nbsp;&nbsp;&nbsp; ```bool```
* Default: &nbsp; true

```useFile```: Define path to directory that holds report file. Report file will be created each day. If ```useFile```
is false, report won't be printed to any file

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "logs"

```useConsole```: Print result to console. If ```useConsole``` is false, report won't be printed to console

* Type: &nbsp;&nbsp;&nbsp; ```bool```
* Default: &nbsp; true

```profilePrefix```: Define Profile's name prefix

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; ""

```profileSuffix```: Define Profile's name suffix

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; ""

```recordPrefix```: Define Record's name prefix

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; ""

```recordSuffix```: Define Record's suffix

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; ""

```timeUnit```: Define unit of time

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "ms"

```timeFormatter```: Define a callback to modify main time metrics. Ignored ```timeUnit``` if this option is defined

* Type: &nbsp;&nbsp;&nbsp; ```array``` | ```string``` | ```null```
* Default: &nbsp; null

```memUnit```: Define unit of memory

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "KB"

```memFormatter```: Define a callback to modify main memory metrics. Ignored ```memUnit``` if this option is defined

* Type: &nbsp;&nbsp;&nbsp; ```array``` | ```string``` | ```null```
* Default: &nbsp;

```topLeftChar```: Define top left corner character

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "╭"

```topRightChar```: Define top right corner character

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "╮"

```bottomLeftChar```: Define bottom left corner character

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "╰"

```bottomRightChar```: Define bottom right corner character

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "╯"

```topForkChar```: Define top fork character

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "┬"

```rightForkChar```: Define right fork character

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "┤"

```bottomForkChar```: Define bottom fork character

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "┴"

```leftForkChar```: Define left fork character

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "├"

```crossChar```: Define cross character

* Type: &nbsp;&nbsp;&nbsp; ```string```
* Default: &nbsp; "┼"

### Printer's hooks

There are some printer's hooks that allow you to interact with report data. To use these hooks, create a class and
extend ```AnalysisPrinter```

```php
Duckstery\Analyzer\AnalysisPrinter::class
```

Then, override methods like this

```php
<?php

use Duckstery\Analyzer\AnalysisPrinter;
use Duckstery\Analyzer\Interfaces\IAProfile;

class MyPrinter extends AnalysisPrinter
{
    public function onPreprocessProfile(IAProfile $profile): void
    {
        // Todo
    }
}
```

These are some hooks that you can use:

```onPreprocessProfile```: Execute before process Profile

* Param: ```IAProfile```: Profile can be modified at this hook

```onPreprocessRecord```: Execute on each Record and before process Record

* Param: ```IARecord```: Record can be modified at this hook

```onEachPreprocessedRecord```: Execute on each Record and after process Record

* Param: ```array```
* Example: Without Profile's extras

```php
$example = [
    "uid" => "654af6159585c",
    "name" => "handle",
    "time" => "2000.605 ms",
    "memory" => "2.5 KB",
]
```

* Example: With Profile's extras (peak)

```php
$example = [
    "uid" => "654af6159585c",
    "name" => "handle",
    "time" => "2000.605 ms",
    "memory" => "2.5 KB",
    "start peak" => ..., // If start = true
    "stop peak" => ..., // If stop = true
    "diff peak" => ..., // If start = stop = diff = true
]
```

```onEachRecordString```: Execute on each Record and after convert Record to string

* Param: ```string```

```onPrintProfileString```: Execute after complete the report

* Param: ```string```: This is the final report
* Note: Modify this won't change your file or console result. If you want to send your report elsewhere, you should
  disable ```useFile``` and ```useConsole``` and define your logic in this hook instead.

## Usage

These are some examples to instruct you to use this package. You will be provided with a static class.

```php
Duckstery\Analyzer\Analyzer::class
```

```Analyzer``` will only measure execution time and memory of your execution. It'll exclude self execution time and
memory out of final result.

The basic approach is placing your logic inside ```start``` and ```stop```. When everything is done, call ```flush``` so Analyzer can generate the report for you.

The Laravel integration has a specific middleware that will execute ```flush``` at the end of request. So you don't need to ```flush``` while using that integration. But in most case, you have to ```flush``` at the end of your program (or at least at the end of the process that you desired to measure).

For any unmentioned situation, you can issue me for more detail.

### Basic

```Analyzer``` only measure execution time and memory of your execution

```php
<?php

use Duckstery\Analyzer\Analyzer;

public class SomeController
{
    public function handle(): void
    {
        // Use Profile: SomeController and start recording
        Analyzer::profile("SomeController")->start("handle");
        
        // Execute process A
        $this->processA();
        // Execute process B
        $this->processB();
        // Execute process C
        $this->processC();
        
        // Stop the latest recording of Profile
        Analyzer::profile("SomeController")->stop();

        // Flush
        Analyzer::flush("SomeController");
        // Or Analyzer::flush(); to flush all Profile
    }
    
    public function processA(): void
    {
        // Use Profile: SomeController and start recording
        Analyzer::profile("SomeController")->start("processA");
    
        // Use 5kb
        
        // Stop the latest recording of Profile
        Analyzer::profile("SomeController")->stop();
    }
    
    public function processB(): void
    {
        // Use Profile: SomeController and start recording
        Analyzer::profile("SomeController")->start("processA");
    
        // Use 0kb
        
        // Stop the latest recording of Profile
        Analyzer::profile("SomeController")->stop();
    }
    
    public function processC(): void
    {
        // Use Profile: SomeController and start recording
        Analyzer::profile("SomeController")->start("processA");
    
        // Executed for 5s
        
        // Stop the latest recording of Profile
        Analyzer::profile("SomeController")->stop();
    }
}
```

Report

```txt
SomeController --------------------
╭───────────────┬──────────┬─────────────┬────────╮
│ Uid           │ Name     │ Time        │ Memory │
├───────────────┼──────────┼─────────────┼────────┤
│ 654af62889e08 │ handle   │ 5036.472 ms │   5 KB │
│ 654af62889e09 │ processA │    6.472 ms │   0 KB │
│ 654af62889e10 │ processB │    6.472 ms │   5 KB │
│ 654af62889e11 │ processC │ 5000.472 ms │   0 KB │
╰───────────────┴──────────┴─────────────┴────────╯
------------------------------
```

### Use default Profile name and extra metrics

Config

```php
$profileExtras = [
    "Default" => [
        "peak" => [
            // Metrics method name's or callback array
            "handler" => "memory_get_peak_usage",
            // Formatter
            "formatter" => [Utils::class, "appendB"],
            // Get at the start
            "start" => true,
            // Get at the end
            "stop" => true,
            // Calculate difference
            "diff" => true
        ]
    ]
]
```

Capture metrics by using default Profile

```php
<?php

use Duckstery\Analyzer\Analyzer;

public class SomeController
{
    public function handle(): void
    {
        $uid = Analyzer::start("SomeController::handle");
        // Or Analyzer::start("SomeController::handle");
        // Todo
        Analyzer::stop($uid);
        // Or Analyzer::stop();

        // Flush
        Analyzer::flush();
    }
}
```

Report

```txt
Default --------------------
╭───────────────┬────────────────────────┬─────────────┬────────┬────────────┬────────────┬───────────╮
│ Uid           │ Name                   │ Time        │ Memory │ Start peak │ Stop peak  │ Diff peak │
├───────────────┼────────────────────────┼─────────────┼────────┼────────────┼────────────┼───────────┤
│ 654af62889e08 │ SomeController::handle │ 2006.472 ms │   0 KB │ 16502872 B │ 16502872 B │       0 B │
╰───────────────┴────────────────────────┴─────────────┴────────┴────────────┴────────────┴───────────╯
----------------------------
```

### Use default Record name

Capture metrics by using default Record name

```php
<?php

use Duckstery\Analyzer\Analyzer;

public class SomeController
{
    public function handle(): void
    {
        $uid = Analyzer::start();
        // Or Analyzer::start();
        // Todo
        Analyzer::stop($uid);
        // Or Analyzer::stop();

        // Flush
        Analyzer::flush();
    }
}
```

Report

```txt
Default --------------------
╭───────────────┬──────────────────┬─────────────┬────────┬────────────┬────────────┬───────────╮
│ Uid           │ Name             │ Time        │ Memory │ Start peak │ Stop peak  │ Diff peak │
├───────────────┼──────────────────┼─────────────┼────────┼────────────┼────────────┼───────────┤
│ 654af62889e08 │ Function: handle │ 2006.472 ms │   0 KB │ 16502872 B │ 16502872 B │       0 B │
╰───────────────┴──────────────────┴─────────────┴────────┴────────────┴────────────┴───────────╯
----------------------------
```

### Use multiple Profile

Capture metrics with multiple Profile

```php
<?php

use Duckstery\Analyzer\Analyzer;

public class SomeController
{
    public function handle(): void
    {
        $uid = Analyzer::startProfile("Profile 1");
        // Or Analyzer::startProfile("Profile 1");
        $this->todo();
        Analyzer::stopProfile("Profile 1", $uid);
        // Or Analyzer::stopProfile("Profile 1");

        // Flush
        Analyzer::flush();
    }
    
    public function todo(): void
    {
        $uid = Analyzer::startProfile("Profile 2");
        // Or Analyzer::startProfile("Profile 2");
        $this->todo();
        Analyzer::stopProfile("Profile 2", $uid);
        // Or Analyzer::stopProfile("Profile 2");
    }
}
```

Report

```txt
Profile 1 --------------------
╭───────────────┬──────────────────┬─────────────┬────────╮
│ Uid           │ Name             │ Time        │ Memory │
├───────────────┼──────────────────┼─────────────┼────────┤
│ 654af62889e08 │ Function: handle │ 2006.472 ms │   0 KB │
╰───────────────┴──────────────────┴─────────────┴────────╯
------------------------------
Profile 2 --------------------
╭───────────────┬────────────────┬─────────────┬────────╮
│ Uid           │ Name           │ Time        │ Memory │
├───────────────┼────────────────┼─────────────┼────────┤
│ 654af62889e09 │ Function: todo │ 2006.472 ms │   0 KB │
╰───────────────┴────────────────┴─────────────┴────────╯
------------------------------
```

## Testing

For testing

```shell
composer run-script test
```

For coverage

```shell
composer run-script test-coverage
```

## Issue

If you discover any security-related issues, bugs or ideas, please feel free to create an issue.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
