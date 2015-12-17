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

        foreach ($gatheredFileData as $fileData) {
            $this->assertArrayHasKey('name', $fileData);
            $this->assertContains($fileData['name'], $files);
            $this->assertArrayHasKey('size', $fileData);
            $this->assertEquals($fileData['size'], filesize($fileData['name']));
        }
    }

    public function testGatherMemoryData()
    {
        $memory_usage = memory_get_peak_usage();
        $allowed_limit = ini_get('memory_limit');
        $profiler = new PhpQuickProfiler();
        $gatheredMemoryData = $profiler->gatherMemoryData();

        $this->assertArrayHasKey('used', $gatheredMemoryData);
        $this->assertEquals($memory_usage, $gatheredMemoryData['used']);
        $this->assertArrayHasKey('allowed', $gatheredMemoryData);
        $this->assertEquals($allowed_limit, $gatheredMemoryData['allowed']);
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
}
