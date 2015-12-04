<?php

/*****************************************
 * Title : PHP Quick Profiler Console Class
 * Author : Created by Ryan Campbell
 * URL : http://particletree.com/features/php-quick-profiler/
 * Description : This class serves as a wrapper to hold onto
 *  various messages and profiling data
 *****************************************/

namespace Particletree\Pqp;

use Exception;

class Console
{

    /** @var  array */
    protected $log = array();

    /** @var  array */
    protected $memory = array();

    /** @var  array */
    protected $error = array();

    /** @var  array */
    protected $speed = array();

    /**
     * Logs data to the console
     * Accepts any data type
     *
     * @param mixed $data
     */
    public function log($data)
    {
        array_push($this->logs, $data);
    }

    /**
     * Logs memory usage of a variable
     * If no parameter is passed in, logs current memory usage
     *
     * @param mixed $object
     * @param string $name
     */
    public function logMemory($object = null, $name = '')
    {
        $memory = memory_get_usage();
        if (!is_null($object)) {
            $memory = strlen(serialize($object));
        }

        array_push($this->memory, array(
            'usage' => $memory,
            'name'  => $name,
            'type'  => gettype($object)
        ));
    }

    /**
     * Logs exception with optional message override
     *
     * @param Exception $exception
     * @param string    $message
     */
    public function logError(Exception $exception, $message = '')
    {
        if (empty($message)) {
            $message = $exception->getMessage();
        }

        array_push($this->error, array(
            'exception' => $exception,
            'message'   => $message
        ));
    }

    /**
     * Logs current time with optional message
     *
     * @param string $name
     */
    public function logSpeed($name = 'Point in Time')
    {
        array_push($this->speed, array(
            'time' => microtime(true),
            'name' => $name,
        ));
    }

    /**
     * Returns the collected logs
     *
     * @returns array
     */
    public function getLogs()
    {
        return array(
            'log'    => $this->log,
            'memory' => $this->memory,
            'error'  => $this->error,
            'speed'  => $this->speed
        );
    }
}
