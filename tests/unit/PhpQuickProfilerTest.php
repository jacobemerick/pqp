<?php

namespace Particletree\Pqp;

use PDO;
use PHPUnit_Framework_Testcase;
use ReflectionClass;
use ReflectionMethod;

class PhpQuickProfilerTest extends PHPUnit_Framework_TestCase
{

    protected static $dbConnection;

    public static function setUpBeforeClass()
    {
        self::$dbConnection = new PDO('sqlite::memory:');
        $createTable = "
            CREATE TABLE IF NOT EXISTS `testing` (
                `id` integer PRIMARY KEY AUTOINCREMENT,
                `title` varchar(60) NOT NULL
            );";
        self::$dbConnection->exec($createTable);

        $hydrateTable = "
            INSERT INTO `testing`
                (`title`)
            VALUES
                ('alpha'),
                ('beta'),
                ('charlie'),
                ('delta');";
        self::$dbConnection->exec($hydrateTable);
    }

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

        $this->assertInternalType('array', $gatheredFileData);
        $this->assertEquals(count($files), count($gatheredFileData));
        foreach ($gatheredFileData as $fileData) {
            $this->assertInternalType('array', $fileData);
            $this->assertArrayHasKey('name', $fileData);
            $this->assertContains($fileData['name'], $files);
            $this->assertArrayHasKey('size', $fileData);
            $this->assertEquals($fileData['size'], filesize($fileData['name']));
        }
    }

    public function testGatherMemoryData()
    {
        $memoryUsage = memory_get_peak_usage();
        $allowedLimit = ini_get('memory_limit');
        $profiler = new PhpQuickProfiler();
        $gatheredMemoryData = $profiler->gatherMemoryData();

        $this->assertInternalType('array', $gatheredMemoryData);
        $this->assertEquals(2, count($gatheredMemoryData));
        $this->assertArrayHasKey('used', $gatheredMemoryData);
        $this->assertEquals($memoryUsage, $gatheredMemoryData['used']);
        $this->assertArrayHasKey('allowed', $gatheredMemoryData);
        $this->assertEquals($allowedLimit, $gatheredMemoryData['allowed']);
    }

    public function testSetProfiledQueries()
    {
        $profiledQueries = $this->dataProfiledQueries();
        $profiler = new PhpQuickProfiler();
        $profiler->setProfiledQueries($profiledQueries);

        $this->assertAttributeEquals($profiledQueries, 'profiledQueries', $profiler);
    }

    public function testGatherQueryData()
    {
        $profiledQueries = $this->dataProfiledQueries();
        $profiledQueriesSql = array();
        $profiledQueriesTime = array();
        foreach ($profiledQueries as $queryData) {
            array_push($profiledQueriesSql, $queryData['sql']);
            array_push($profiledQueriesTime, $queryData['time']);
        }

        $profiler = new PhpQuickProfiler();
        $profiler->setProfiledQueries($profiledQueries);
        $gatheredQueryData = $profiler->gatherQueryData(self::$dbConnection);

        $this->assertInternalType('array', $gatheredQueryData);
        $this->assertEquals(count($profiledQueries), count($gatheredQueryData));
        foreach ($gatheredQueryData as $queryData) {
            $this->assertInternalType('array', $queryData);
            $this->assertArrayHasKey('sql', $queryData);
            $this->assertContains($queryData['sql'], $profiledQueriesSql);
            $this->assertArrayHasKey('explain', $queryData);
            $this->assertInternaltype('array', $queryData['explain']);
            $this->assertGreaterThan(0, count($queryData['explain']));
            $this->assertArrayHasKey('time', $queryData);
            $this->assertContains($queryData['time'], $profiledQueriesTime);
        }
    }

    /**
     * @dataProvider dataProfiledQueries
     */
    public function testExplainQuery($sql, $parameters)
    {
        $profiler = new PhpQuickProfiler();
        $reflectedMethod = $this->getAccessibleMethod($profiler, 'explainQuery');

        $explainedQuery = $reflectedMethod->invokeArgs(
            $profiler,
            array(self::$dbConnection, $sql, $parameters)
        );
        $this->assertInternalType('array', $explainedQuery);
        $this->assertGreaterThan(0, count($explainedQuery));
    }

    /**
     * @expectedException Exception
     */
    public function testExplainQueryBadQueryException()
    {
        $invalidQuery = 'SELECT * FROM `fake_table`';
        $profiler = new PhpQuickProfiler();
        $reflectedMethod = $this->getAccessibleMethod($profiler, 'explainQuery');

        $reflectedMethod->invokeArgs(
            $profiler,
            array(self::$dbConnection, $invalidQuery)
        );
    }

    /**
     * @expectedException Exception
     */
    public function testExplainQueryBadParametersException()
    {
        $query = 'SELECT * FROM `testing` WHERE `title` = :title';
        $invalidParams = array('id' => 1);
        $profiler = new PhpQuickProfiler();
        $reflectedMethod = $this->getAccessibleMethod($profiler, 'explainQuery');

        $reflectedMethod->invokeArgs(
            $profiler,
            array(self::$dbConnection, $query, $invalidParams)
        );
    }

    /**
     * @dataProvider dataConnectionDrivers
     */
    public function testGetExplainQuery($driver, $prefix)
    {
        $query = 'SELECT * FROM `testing`';
        $profiler = new PhpQuickProfiler();
        $reflectedMethod = $this->getAccessibleMethod($profiler, 'getExplainQuery');

        $explainQuery = $reflectedMethod->invokeArgs(
            $profiler,
            array($query, $driver)
        );

        $explainPrefix = str_replace($query, '', $explainQuery);
        $explainPrefix = trim($explainPrefix);
        $this->assertEquals($prefix, $explainPrefix);
    }

    /**
     * @expectedException Exception
     */
    public function testGetExplainQueryUnsupportedDriver()
    {
        $query = 'SELECT * FROM `testing`';
        $unsupportedDriver = 'zz';
        $profiler = new PhpQuickProfiler();
        $reflectedMethod = $this->getAccessibleMethod($profiler, 'getExplainQuery');

        $reflectedMethod->invokeArgs(
            $profiler,
            array($query, $unsupportedDriver)
        );
    }

    public function testGatherSpeedData()
    {
        $elapsedTime = 1.234;
        $startTime = microtime(true) - $elapsedTime;
        $allowedTime = ini_get('max_execution_time');
        $profiler = new PhpQuickProfiler($startTime);
        $gatheredSpeedData = $profiler->gatherSpeedData();

        $this->assertInternalType('array', $gatheredSpeedData);
        $this->assertEquals(2, count($gatheredSpeedData));
        $this->assertArrayHasKey('elapsed', $gatheredSpeedData);
        $this->assertEquals($elapsedTime, $gatheredSpeedData['elapsed']);
        $this->assertArrayHasKey('allowed', $gatheredSpeedData);
        $this->assertEquals($allowedTime, $gatheredSpeedData['allowed']);
    }

    public function dataProfiledQueries()
    {
        return array(
            array(
              'sql' => "SELECT * FROM testing",
              'parameters' => array(),
              'time' => 25
            ),
            array(
              'sql' => "SELECT id FROM testing WHERE title = :title",
              'parameters' => array('title' => 'beta'),
              'time' => 5
            )
        );
    }

    public function dataConnectionDrivers()
    {
        return array(
            array(
                'driver' => 'mysql',
                'prefix' => 'EXPLAIN'
            ),
            array(
                'driver' => 'sqlite',
                'prefix' => 'EXPLAIN QUERY PLAN'
            )
        );
    }

    protected function getAccessibleMethod(PhpQuickProfiler $profiler, $methodName)
    {
        $reflectedConsole = new ReflectionClass(get_class($profiler));
        $reflectedMethod = $reflectedConsole->getMethod($methodName);
        $reflectedMethod->setAccessible(true);
        return $reflectedMethod;
    }

    public static function tearDownAfterClass()
    {
        self::$dbConnection = null;
    }
}
