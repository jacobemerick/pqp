<?php

/*****************************************
 * Title : PHP Quick Profiler Class
 * Author : Created by Ryan Campbell
 * URL : http://particletree.com/features/php-quick-profiler/
 * Description : This class processes the logs and organizes the data
 *  for output to the browser. Initialize this class with a start time
 *  at the beginning of your code, and then call the display method when
 *  your code is terminating.
*****************************************/

namespace Particletree\Pqp;

class PhpQuickProfiler
{

    /** @var  integer */
    protected $startTime;

    /** @var  Console */
    protected $console;

    /** @var  Display */
    protected $display;

    /** @var  array */
    protected $profiledQueries = array();

    /**
     * @param integer $startTime
     */
    public function __construct($startTime = null)
    {
        if (is_null($startTime)) {
            $startTime = microtime(true);
        }
        $this->startTime = $startTime;
    }

    /**
     * @param Console $console
     */
    public function setConsole(Console $console)
    {
        $this->console = $console;
    }

    /**
     * @param Display $display
     */
    public function setDisplay(Display $display)
    {
        $this->display = $display;
    }

    /**
     * Get data about files loaded for the application to current point
     *
     * @returns array
     */
    public function gatherFileData()
    {
        $files = get_included_files();
        $data = array();
        foreach ($files as $file) {
            array_push($data, array(
                'name' => $file,
                'size' => filesize($file)
            ));
        }
        return $data;
    }

    /**
     * Get data about memory usage of the application
     *
     * @returns array
     */
    public function gatherMemoryData()
    {
        $usedMemory = memory_get_peak_usage();
        $allowedMemory = ini_get('memory_limit');
        return array(
            'used'    => $usedMemory,
            'allowed' => $allowedMemory
        );
    }

    /**
     * @param array $profiled_queries
     */
    public function setProfiledQueries(array $profiledQueries)
    {
        $this->profiledQueries = $profiledQueries;
    }

    /**
     * Get data about sql usage of the application
     *
     * @param object $dbConnection
     * @returns array
     */
    public function gatherQueryData($dbConnection)
    {
        if (empty($this->profiledQueries) && property_exists($dbConnection, 'queries')) {
            $this->setProfiledQueries($dbConnection->queries);
        }

        $data = array();
        foreach ($this->profiledQueries as $query) {
            if ($query['function'] !== 'perform') {
                continue;
            }

            array_push($data, array(
                'sql'     => $query['statement'],
                'explain' => $this->explainQuery($query['statement'], $query['bind_values']),
                'time'    => $query['duration']
            ));
        }
        return $data;
    }

    /**
     * Attempts to explain a query
     *
     * @param string $query
     * @param array  $parameters
     * @return array
     */
    protected function explainQuery($query, $parameters)
    {
        $query = "EXPLAIN {$query}";
        try {
            $statement = $this->dbConnection->prepare($query);
            $statement->execute($parameters);
            return $statement->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return '';
    }

    /**
     * Get data about speed of the application
     *
     * @returns array
     */
    public function gatherSpeedData()
    {
        $elapsedTime = microtime(true) - $this->startTime;
        $allowedTime = ini_get('max_execution_time');
        return array(
            'elapsed' => $elapsedTime,
            'allowed' => $allowedTime
        );
    }

    /**
     * Triggers end display of the profiling data
     *
     * @param object $dbConnection
     */
    public function display($dbConnection = null)
    {
        if (!isset($this->display)) {
            throw new Exception('Display object has not been injected into Profiler');
        }

        $this->display->setConsole($this->console);
        $this->display->setFileData($this->gatherFileData());
        $this->display->setMemoryData($this->gatherMemoryData());
        $this->display->setQueryData($this->gatherQueryData($dbConnection));
        $this->display->setSpeedData($this->gatherSpeedData());

        $this->display();
    }
}
