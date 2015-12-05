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
    protected $store = array();

    /**
     * Logs data to the console
     * Accepts any data type
     *
     * @param mixed $data
     */
    public function log($data)
    {
        array_push($this->store, array(
          'data' => $data,
          'type' => 'log'
        ));
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

        array_push($this->store, array(
            'name'      => $name,
            'data'      => $memory,
            'data_type' => gettype($object),
            'type'      => 'memory'
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

        array_push($this->store, array(
            'data' => $message,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'type' => 'error'
        ));
    }

    /**
     * Logs current time with optional message
     *
     * @param string $name
     */
    public function logSpeed($name = 'Point in Time')
    {
        array_push($this->store, array(
            'data' => microtime(true),
            'name' => $name,
            'type' => 'speed'
        ));
    }

    /**
     * Returns the collected logs
     *
     * @returns array
     */
    public function getLogs()
    {
        return $this->store;
    }
}
