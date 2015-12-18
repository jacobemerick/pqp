<?php

namespace Particletree\Pqp;

use PHPUnit_Framework_Testcase;

// namespace hack on microtime functionality
function microtime()
{
    return 1450355136.5706;
}

// namespace hack on included files functionality
function get_included_files()
{
    return array(
      'index.php',
      'src/Class.php'
    );
}

// namespace hack on filesize
function filesize($filename)
{
    return strlen($filename) * 100;
}

// namespace hack on memory usage
function memory_get_peak_usage()
{
    return 123456789;
}

// namespace hack on ini settings
function ini_get($setting)
{
    if ($setting == 'memory_limit') {
        return '128M';
    } elseif ($setting == 'max_execution_time') {
        return '30';
    }
    return \ini_get($setting);
}

class PhpQuickProfilerTest extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $startTime = microtime(true);

        $profiler = new PhpQuickProfiler();
        $this->assertAttributeEquals($startTime, 'startTime', $profiler);

        $profiler = new PhpQuickProfiler($startTime);
        $this->assertAttributeEquals($startTime, 'startTime', $profiler);
    }

    public function testSetConsole()
    {
        $console = new Console();
        $profiler = new PhpQuickProfiler();
        $profiler->setConsole($console);

        $this->assertAttributeSame($console, 'console', $profiler);
    }

    public function testSetDisplay()
    {
        $display = new Display();
        $profiler = new PhpQuickProfiler();
        $profiler->setDisplay($display);

        $this->assertAttributeSame($display, 'display', $profiler);
    }

    public function testGatherFileData()
    {
        $files = get_included_files();
        $profiler = new PhpQuickProfiler();
        $gatheredFileData = $profiler->gatherFileData();

        $this->assertInternalType('array', $gatheredFileData);
        $this->assertEquals(count($files), count($gatheredFileData));
        foreach ($gatheredFileData as $fileData) {
            $this->assertInternalType('array', $fileData);
            $this->assertArrayHasKey('name', $fileData);
            $this->assertContains($fileData['name'], $files);
            $this->assertArrayHasKey('size', $fileData);
            $this->assertEquals($fileData['size'], filesize($fileData['name']));
        }
    }

    public function testGatherMemoryData()
    {
        $memoryUsage = memory_get_peak_usage();
        $allowedLimit = ini_get('memory_limit');
        $profiler = new PhpQuickProfiler();
        $gatheredMemoryData = $profiler->gatherMemoryData();

        $this->assertInternalType('array', $gatheredMemoryData);
        $this->assertEquals(2, count($gatheredMemoryData));
        $this->assertArrayHasKey('used', $gatheredMemoryData);
        $this->assertEquals($memoryUsage, $gatheredMemoryData['used']);
        $this->assertArrayHasKey('allowed', $gatheredMemoryData);
        $this->assertEquals($allowedLimit, $gatheredMemoryData['allowed']);
    }

    public function testSetProfiledQueries()
    {
        $profiledQueries = array(
            'sql' => 'SELECT * FROM example',
            'time' => 25
        );
        $profiler = new PhpQuickProfiler();
        $profiler->setProfiledQueries($profiledQueries);

        $this->assertAttributeEquals($profiledQueries, 'profiledQueries', $profiler);
    }

    public function testGatherSpeedData()
    {
        $elapsedTime = 1.234;
        $startTime = microtime(true) - $elapsedTime;
        $allowedTime = ini_get('max_execution_time');
        $profiler = new PhpQuickProfiler($startTime);
        $gatheredSpeedData = $profiler->gatherSpeedData();

        $this->assertInternalType('array', $gatheredSpeedData);
        $this->assertEquals(2, count($gatheredSpeedData));
        $this->assertArrayHasKey('elapsed', $gatheredSpeedData);
        $this->assertEquals($elapsedTime, $gatheredSpeedData['elapsed']);
        $this->assertArrayHasKey('allowed', $gatheredSpeedData);
        $this->assertEquals($allowedTime, $gatheredSpeedData['allowed']);
    }
}
