<?php

namespace Particletree\Pqp;

use Exception;
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

    public function testSetStartTime()
    {
        $startTime = microtime(true);
        $display = new Display();
        $display->setStartTime($startTime);

        $this->assertAttributeEquals($startTime, 'startTime', $display);
    }

    public function testSetConsole()
    {
        $console = new Console();
        $display = new Display();
        $display->setConsole($console);

        $this->assertAttributeSame($console, 'console', $display);
    }

    public function testSetMemoryData()
    {
        $memoryData = array(
            'used'    => memory_get_peak_usage(),
            'allowed' => ini_get('memory_limit')
        );
        $display = new Display();
        $display->setMemoryData($memoryData);

        $this->assertAttributeEquals($memoryData, 'memoryData', $display);
    }

    public function testSetQueryData()
    {
        $queryData = array(
            'sql'     => 'SELECT * FROM testing',
            'explain' => array(
                'key' => 'value'
            ),
            'time'    => 300
        );
        $display = new Display();
        $display->setQueryData($queryData);

        $this->assertAttributeEquals($queryData, 'queryData', $display);
    }

    public function testSetSpeedData()
    {
        $speedData = array(
            'elapsed' => 1.234,
            'allowed' => 30
        );
        $display = new Display();
        $display->setSpeedData($speedData);

        $this->assertAttributeEquals($speedData, 'speedData', $display);
    }

    public function testGetConsoleMeta()
    {
        $expectedMeta = array(
            'log'    => 1,
            'memory' => 0,
            'error'  => 0,
            'speed'  => 2
        );
        $console = new Console();
        $console->log('testing words');
        $console->logSpeed('now');
        $console->logSpeed();
        $display = new Display();
        $display->setConsole($console);
        $reflectedMethod = $this->getAccessibleMethod($display, 'getConsoleMeta');

        $consoleMeta = $reflectedMethod->invoke($display);
        $this->assertEquals($expectedMeta, $consoleMeta);
    }

    public function testGetConsoleMessages()
    {
        $console = new Console();
        $testLog = 'testing more words';
        $console->log($testLog);
        $console->logMemory();
        $testException = new Exception('test exception');
        $console->logError($testException);
        $console->logSpeed();
        $display = new Display();
        $display->setConsole($console);
        $reflectedMethod = $this->getAccessibleMethod($display, 'getConsoleMessages');

        $consoleMessages = $reflectedMethod->invoke($display);
        foreach ($consoleMessages as $message) {
            $this->assertArrayHasKey('message', $message);
            $this->assertInternalType('string', $message['message']);
            $this->assertArrayHasKey('type', $message);
            $this->assertInternalType('string', $message['type']);
            switch ($message['type']) {
                case 'log':
                    $expectedMessage = print_r($testLog, true);
                    $this->assertEquals($expectedMessage, $message['message']);
                    break;
                case 'memory':
                    $expectedMessage = 'PHP';
                    $this->assertEquals($expectedMessage, $message['message']);
                    $this->assertArrayHasKey('data', $message);
                    $this->assertInternalType('string', $message['data']);
                    $expectedData = memory_get_usage();
                    $reflectedMethod = $this->getAccessibleMethod($display, 'getReadableMemory');
                    $expectedData = $reflectedMethod->invokeArgs($display, array($expectedData));
                    $this->assertEquals($expectedData, $message['data']);
                    break;
                case 'error':
                    $expectedMessage = sprintf(
                        "Line %s: %s in %s",
                        $testException->getLine(),
                        $testException->getMessage(),
                        $testException->getFile()
                    );
                    $this->assertEquals($expectedMessage, $message['message']);
                    break;
                case 'speed':
                    $expectedMessage = 'Point in Time';
                    $this->assertEquals($expectedMessage, $message['message']);
                    $this->assertArrayHasKey('data', $message);
                    $this->assertInternalType('string', $message['data']);
                    $expectedData = microtime(true);
                    $reflectedMethod = $this->getAccessibleMethod($display, 'getReadableTime');
                    $expectedData = $reflectedMethod->invokeArgs($display, array($expectedData));
                    $this->assertEquals($expectedData, $message['data']);
                    break;
            }
        }
    }

    public function testGetSpeedMeta()
    {
        $elapsedTime = 1234.678;
        $allowedTime = 30;
        $display = new Display();
        $display->setSpeedData(array(
            'elapsed' => $elapsedTime,
            'allowed' => $allowedTime
        ));
        $reflectedMethod = $this->getAccessibleMethod($display, 'getReadableTime');
        $elapsedTime = $reflectedMethod->invokeArgs($display, array($elapsedTime));
        $allowedTime = $reflectedMethod->invokeArgs($display, array($allowedTime, 0));
        $expectedMeta = array(
            'elapsed' => $elapsedTime,
            'allowed' => $allowedTime
        );

        $reflectedMethod = $this->getAccessibleMethod($display, 'getSpeedMeta');
        $speedMeta = $reflectedMethod->invoke($display);
        $this->assertEquals($expectedMeta, $speedMeta);
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
