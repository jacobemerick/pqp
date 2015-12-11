<?php

namespace Particletree\Pqp;

use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class ConsoleTest extends PHPUnit_Framework_TestCase
{

    public function __construct()
    {
    }

    public function testLog()
    {
        $data = array(
            'key' => 'value'
        );

        $console = new Console();
        $console->log($data);
        $store = $this->getProtectedStore($console);
        $log = array_pop($store);

        $this->assertSame($data, $log['data']);
        $this->assertEquals('log', $log['type']);
    }

    public function testLogMemory()
    {
        $data = array(
            'key' => 'value'
        );
        $memory = strlen(serialize($data));
        $name = 'Test Array';

        $console = new Console();
        $console->logMemory($data, $name);
        $store = $this->getProtectedStore($console);
        $log = array_pop($store);

        $this->assertEquals($name, $log['name']);
        $this->assertEquals($memory, $log['data']);
        $this->assertEquals('array', $log['data_type']);
        $this->assertEquals('memory', $log['type']);
    }

    public function testLogError()
    {
        $error = new Exception('Test Exception');

        $console = new Console();
        $console->logError($error);
        $store = $this->getProtectedStore($console);
        $log = array_pop($store);

        $this->assertEquals($error->getMessage(), $log['data']);
        $this->assertEquals($error->getFile(), $log['file']);
        $this->assertEquals($error->getLine(), $log['line']);
        $this->assertEquals('error', $log['type']);

        $error = new Exception('Test Exception');
        $message = 'override message';

        $console = new Console();
        $console->logError($error, $message);
        $store = $this->getProtectedStore($console);
        $log = array_pop($store);

        $this->assertEquals($message, $log['data']);
    }

    public function testLogSpeed()
    {
        $name = 'Test Speed';

        $console = new Console();
        $console->logSpeed($name);
        $store = $this->getProtectedStore($console);
        $log = array_pop($store);

        $this->assertEquals($name, $log['name']);
        $this->assertEquals('speed', $log['type']);
    }

    public function testGetLogs()
    {
        $store = array(
            array(
                'data' => 'a string',
                'type' => 'log'
            ),
            array(
                'name' => '',
                'data' => 123,
                'data_type' => 'array',
                'type' => 'memory'
            )
        );

        $console = new Console();

        $reflectedConsole = new ReflectionClass(get_class($console));
        $reflectedProperty = $reflectedConsole->getProperty('store');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($console, $store);

        $this->assertSame($store, $console->getLogs());
    }

    protected function getProtectedStore(Console $console)
    {
        $reflectedConsole = new ReflectionClass(get_class($console));
        $reflectedProperty = $reflectedConsole->getProperty('store');
        $reflectedProperty->setAccessible(true);
        return $reflectedProperty->getValue($console);
    }
}
