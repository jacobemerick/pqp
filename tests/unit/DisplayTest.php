<?php

namespace Particletree\Pqp;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class DisplayTest extends PHPUnit_Framework_TestCase
{

    public function testGetReadableTime()
    {
        $timeTest = array(
            '.032432' => '32.432 ms',
            '24.3781' => '24.378 s',
            '145.123' => '2.419 m'
        );
        $display = new Display();
        $reflectedMethod = $this->getAccessibleMethod($display, 'getReadableTime');

        foreach ($timeTest as $rawTime => $expectedReadableTime) {
            $readableTime = $reflectedMethod->invokeArgs($display, array($rawTime));
            $this->assertEquals($expectedReadableTime, $readableTime);
        }
    }

    public function testGetReadableMemory()
    {
        $memoryTest = array(
            '314'     => '314 b',
            '7403'    => '7.23 k',
            '2589983' => '2.47 M'
        );
        $display = new Display();
        $reflectedMethod = $this->getAccessibleMethod($display, 'getReadableMemory');

        foreach ($memoryTest as $rawMemory => $expectedReadableMemory) {
            $readableMemory = $reflectedMethod->invokeArgs($display, array($rawMemory));
            $this->assertEquals($expectedReadableMemory, $readableMemory);
        }
    }

    protected function getAccessibleMethod(Display $display, $methodName)
    {
        $reflectedConsole = new ReflectionClass(get_class($display));
        $reflectedMethod = $reflectedConsole->getMethod($methodName);
        $reflectedMethod->setAccessible(true);
        return $reflectedMethod;
    }
}
