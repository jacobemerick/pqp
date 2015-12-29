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

    /**
     * @dataProvider dataConsoleStore
     */
    public function testGetConsoleMeta($consoleStore, $expectedMeta, $expectedMessages)
    {
        $console = new Console();
        $this->setInternalProperty($console, 'store', $consoleStore);
        $display = new Display();
        $display->setConsole($console);
        $reflectedMethod = $this->getAccessibleMethod($display, 'getConsoleMeta');

        $consoleMeta = $reflectedMethod->invoke($display);
        $this->assertEquals($expectedMeta, $consoleMeta);
    }

    /**
     * @dataProvider dataConsoleStore
     */
    public function testGetConsoleMessages($consoleStore, $expectedMeta, $expectedMessages)
    {
        $console = new Console();
        $this->setInternalProperty($console, 'store', $consoleStore);
        $display = new Display();
        $display->setConsole($console);
        $reflectedMethod = $this->getAccessibleMethod($display, 'getConsoleMessages');

        $consoleMessages = $reflectedMethod->invoke($display);
        $this->assertEquals($expectedMessages, $consoleMessages);
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

    /**
     * @dataProvider dataQueryData
     */
    public function testGetQueryMeta($queryData, $expectedMeta, $expectedList)
    {
        $display = new Display();
        $display->setQueryData($queryData);
        $reflectedMethod = $this->getAccessibleMethod($display, 'getQueryMeta');

        $queryMeta = $reflectedMethod->invoke($display);
        $this->assertEquals($expectedMeta, $queryMeta);
    }

    /**
     * @dataProvider dataQueryData
     */
    public function testGetQueryList($queryData, $expectedMeta, $expectedList)
    {
        $display = new Display();
        $display->setQueryData($queryData);
        $reflectedMethod = $this->getAccessibleMethod($display, 'getQueryList');

        $queryList = $reflectedMethod->invoke($display);
        $this->assertEquals($expectedList, $queryList);
    }

    public function testGetMemoryMeta()
    {
        $usedMemory = 123456;
        $allowedMemory = '128M';
        $display = new Display();
        $display->setMemoryData(array(
            'used'    => $usedMemory,
            'allowed' => $allowedMemory
        ));
        $reflectedMethod = $this->getAccessibleMethod($display, 'getReadableMemory');
        $usedMemory = $reflectedMethod->invokeArgs($display, array($usedMemory));
        $expectedMeta = array(
            'used'    => $usedMemory,
            'allowed' => $allowedMemory
        );

        $reflectedMethod = $this->getAccessibleMethod($display, 'getMemoryMeta');
        $memoryMeta = $reflectedMethod->invoke($display);
        $this->assertEquals($expectedMeta, $memoryMeta);
    }

    /**
     * @dataProvider dataFileData
     */
    public function testGetFileMeta($fileData, $expectedMeta, $expectedList)
    {
        $display = new Display();
        $display->setFileData($fileData);
        $reflectedMethod = $this->getAccessibleMethod($display, 'getFileMeta');

        $fileMeta = $reflectedMethod->invoke($display);
        $this->assertEquals($expectedMeta, $fileMeta);
    }

    /**
     * @dataProvider dataFileData
     */
    public function testGetFileList($fileData, $expectedMeta, $expectedList)
    {
        $display = new Display();
        $display->setFileData($fileData);
        $reflectedMethod = $this->getAccessibleMethod($display, 'getFileList');

        $fileList = $reflectedMethod->invoke($display);
        $this->assertEquals($expectedList, $fileList);
    }

    public function testFilterMessages()
    {
        $display = new Display();
        $reflectedMethod = $this->getAccessibleMethod($display, 'filterMessages');

        $filteredMessages = $reflectedMethod->invokeArgs($display, array(array(array(
            'type' => 'remove'
        )), 'keep'));
        $this->assertEmpty($filteredMessages);
        $filteredMessages = $reflectedMethod->invokeArgs($display, array(array(array(
            'type' => 'keep'
        )), 'keep'));
        $this->assertCount(1, $filteredMessages);
    }

    public function testGetReadableTime()
    {
        $timeTest = array(
            '.032432' => '32 ms',
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

    public function dataConsoleStore()
    {
        $testException = new Exception('testing');
        $display = new Display();
        $reflectedTime = $this->getAccessibleMethod($display, 'getReadableTime');
        $reflectedMemory = $this->getAccessibleMethod($display, 'getReadableMemory');

        return array(
            array(
                'store' => array(
                    array(
                        'data' => 'testing message',
                        'type' => 'log'
                    ),
                    array(
                        'name' => 'now',
                        'data' => microtime(true),
                        'type' => 'speed'
                    ),
                    array(
                        'name' => 'little later',
                        'data' => microtime(true) + 1,
                        'type' => 'speed'
                    ),
                    array(
                        'name' => 'invalid key',
                        'type' => 'foo'
                    )
                ),
                'meta' => array(
                    'log'    => 1,
                    'memory' => 0,
                    'error'  => 1,
                    'speed'  => 2
                ),
                'messages' => array(
                    array(
                        'message' => 'testing message',
                        'type'    => 'log'
                    ),
                    array(
                        'message' => 'now',
                        'data'    => $reflectedTime->invokeArgs($display, array(microtime(true))),
                        'type'    => 'speed'
                    ),
                    array(
                        'message' => 'little later',
                        'data'    => $reflectedTime->invokeArgs($display, array(microtime(true) + 1)),
                        'type'    => 'speed'
                    ),
                    array(
                        'message' => 'Unrecognized console log type: foo',
                        'type'    => 'error'
                    )
                )
            ),
            array(
                'store' => array(
                    array(
                        'data' => 'another testing message',
                        'type' => 'log'
                    ),
                    array(
                        'name'      => 'test array',
                        'data'      => strlen(serialize(array('key' => 'value'))),
                        'data_type' => 'array',
                        'type'      => 'memory'
                    ),
                    array(
                        'name'      => 'memory usage test',
                        'data'      => memory_get_usage(),
                        'data_type' => '',
                        'type'      => 'memory'
                    ),
                    array(
                        'data' => $testException->getMessage(),
                        'file' => $testException->getFile(),
                        'line' => $testException->getLine(),
                        'type' => 'error'
                    )
                ),
                'meta' => array(
                    'log'    => 1,
                    'memory' => 2,
                    'error'  => 1,
                    'speed'  => 0
                ),
                'messages' => array(
                    array(
                        'message' => 'another testing message',
                        'type'    => 'log'
                    ),
                    array(
                        'message' => 'array: test array',
                        'data'    => $reflectedMemory->invokeArgs(
                            $display,
                            array(strlen(serialize(array('key' => 'value'))))
                        ),
                        'type'    => 'memory'
                    ),
                    array(
                        'message' => 'memory usage test',
                        'data'    => $reflectedMemory->invokeArgs($display, array(memory_get_usage())),
                        'type'    => 'memory'
                    ),
                    array(
                        'message' => sprintf(
                            'Line %s: %s in %s',
                            $testException->getLine(),
                            $testException->getMessage(),
                            $testException->getFile()
                        ),
                        'type'    => 'error'
                    )
                )
            )
        );
    }

    public function dataQueryData()
    {
        $display = new Display();
        $reflectedTime = $this->getAccessibleMethod($display, 'getReadableTime');

        return array(
            array(
                'data' => array(
                    array(
                        'sql'     => "SELECT * FROM testing",
                        'explain' => array('empty_key' => ''),
                        'time'    => 25
                    ),
                    array(
                        'sql'     => "SELECT id FROM testing WHERE title = :title",
                        'explain' => array('key' => 'value'),
                        'time'    => 5
                    )
                ),
                'meta' => array(
                    'count'   => 2,
                    'time'    => $reflectedTime->invokeArgs($display, array(30)),
                    'slowest' => $reflectedTime->invokeArgs($display, array(25)),
                ),
                'list' => array(
                    array(
                        'message'  => 'SELECT * FROM testing',
                        'sub_data' => array(),
                        'data'     => $reflectedTime->invokeArgs($display, array(25))
                    ),
                    array(
                        'message'  => 'SELECT id FROM testing WHERE title = :title',
                        'sub_data' => array('key' => 'value'),
                        'data'     => $reflectedTime->invokeArgs($display, array(5))
                    )
                )
            )
        );
    }

    public function dataFileData()
    {
        $display = new Display();
        $reflectedMemory = $this->getAccessibleMethod($display, 'getReadableMemory');

        return array(
            array(
                'data' => array(
                    array(
                        'name' => 'test-file',
                        'size' => 1234
                    ),
                    array(
                        'name' => 'test-file-2',
                        'size' => 66
                    )
                ),
                'meta' => array(
                    'count'   => 2,
                    'size'    => $reflectedMemory->invokeArgs($display, array(1300)),
                    'largest' => $reflectedMemory->invokeArgs($display, array(1234)),
                ),
                'list' => array(
                    array(
                        'message' => 'test-file',
                        'data'    => $reflectedMemory->invokeArgs($display, array(1234))
                    ),
                    array(
                        'message' => 'test-file-2',
                        'data'    => $reflectedMemory->invokeArgs($display, array(66))
                    )
                )
            )
        );
    }

    protected function setInternalProperty($class, $property, $value)
    {
        $reflectedClass = new ReflectionClass(get_class($class));
        $reflectedProperty = $reflectedClass->getProperty($property);
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($class, $value);
    }

    protected function getAccessibleMethod($class, $method)
    {
        $reflectedClass = new ReflectionClass(get_class($class));
        $reflectedMethod = $reflectedClass->getMethod($method);
        $reflectedMethod->setAccessible(true);
        return $reflectedMethod;
    }
}
