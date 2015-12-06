<?php

use Particletree\Pqp\Console;
use Particletree\Pqp\PhpQuickProfiler;

class PhpQuickProfilerTest extends PHPUnit_Framework_TestCase
{

    public function __construct()
    {
    }

    public function testConstruct()
    {
        $console = new Console();
        $startTime = microtime(true);

        $profiler = new PhpQuickProfiler($console, $startTime);

        $this->assertAttributeSame($console, 'console', $profiler);
        $this->assertAttributeEquals($startTime, 'startTime', $profiler);
    }
}
