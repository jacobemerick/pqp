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
