<?php

namespace Particletree\Pqp;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class DisplayTest extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $display = new Display();
        $reflectedDisplay = new ReflectionClass(get_class($display));
        $reflectedProperty = $reflectedDisplay->getProperty('defaults');
        $reflectedProperty->setAccessible(true);
        $defaults = $reflectedProperty->getValue($display);

        $display = new Display();
        $this->assertAttributeEquals($defaults, 'options', $display);

        $options = array(
            'script_path' => 'testing/testing.js',
            'fake_key' => 'foo bar'
        );
        $expectedOptions = array_intersect_key($options, $defaults);
        $expectedOptions = array_replace($defaults, $expectedOptions);
        $display = new Display($options);
        $this->assertAttributeEquals($expectedOptions, 'options', $display);
    }

    public function testGetReadableTime()
    {
        $timeTest = array(
            '.032432' => '32.432 ms',
            '24.3781' => '24.378 s',
            '145.123' => '2.419 m'
        );
        $display = new Display();
        $reflectedMethod = $this->getAccessibleMethod($display, 'getReadableTime');

        foreach ($timeTest as $rawTime => $expectedTime) {
            $readableTime = $reflectedMethod->invokeArgs($display, array($rawTime));
            $this->assertEquals($expectedTime, $readableTime);
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

        foreach ($memoryTest as $rawMemory => $expectedMemory) {
            $readableMemory = $reflectedMethod->invokeArgs($display, array($rawMemory));
            $this->assertEquals($expectedMemory, $readableMemory);
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
