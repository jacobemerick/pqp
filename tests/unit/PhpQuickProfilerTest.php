<?php

namespace Particletree\Pqp;

use PHPUnit_Framework_Testcase;

class PhpQuickProfilerTest extends PHPUnit_Framework_TestCase
{

    public function __construct()
    {
    }

    public function testConstruct()
    {
        $console = new Console();
        $startTime = microtime(true);

        $profiler = new PhpQuickProfiler($console, null, $startTime);

        $this->assertAttributeSame($console, 'console', $profiler);
        $this->assertAttributeEquals($startTime, 'startTime', $profiler);
    }
}
