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

    /** @var  Console */
    protected $console;

    /** @var  integer */
    protected $startTime;

    /** @var  object */
    protected $pdo;

    /**
     * @param Console $console
     * @param object  $pdo
     * @param integer $startTime
     */
    public function __construct(Console $console, $pdo = null, $startTime = null)
    {
        $this->console = $console;
        $this->pdo = $pdo;

        if (is_null($startTime)) {
            $startTime = microtime(true);
        }
        $this->startTime = $startTime;
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
     * Get data about sql usage of the application
     *
     * @param array $profiledQueries
     * @returns array
     */
    public function gatherQueryData(array $profiledQueries)
    {
        $data = array();
        foreach ($profiledQueries as $query) {
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
            $statement = $this->pdo->prepare($query);
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
     * @param Display $display
     * @param array   $profiledQueries
     */
    public function display(Display $display, array $profiledQueries = array())
    {
        $display->setConsole($this->console);
        $display->setFileData($this->gatherFileData());
        $display->setMemoryData($this->gatherMemoryData());
        $display->setQueryData($this->gatherQueryData($profiledQueries));
        $display->setSpeedData($this->gatherSpeedData());

        $display();
    }
}
