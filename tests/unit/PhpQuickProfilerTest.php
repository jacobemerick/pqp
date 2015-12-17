<?php

namespace Particletree\Pqp;

use PHPUnit_Framework_Testcase;

class PhpQuickProfilerTest extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $startTime = microtime(true);
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
