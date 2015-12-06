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

    public function testGetReadableTime()
    {
        $test_input = array(
            '.032432' => '32.432 ms',
            '24.3781' => '24.378 s',
            '145.123' => '2.419 m'
        );

        foreach ($test_input as $input => $expected_return) {
            $this->assertEquals($expected_return, PhpQuickProfiler::getReadableTime($input));
        }
    }
}
