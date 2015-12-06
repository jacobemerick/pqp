<?php

/*****************************************
 * Title : Php Quick Profiler Display Class
 * Author : Created by Ryan Campbell
 * URL : http://particletree.com/features/php-quick-profiler/
 * Description : This is a hacky way of pushing profiling logic to the
 *  PQP HTML. This is great because it will just work in your project,
 *  but it is hard to maintain and read.
*****************************************/

namespace Particletree\Pqp;

class Display
{

    /** @var  array */
    protected $output;

    public function __construct()
    {
    }

    public function setConsoleData(array $console_data)
    {
        $this->output['console'] = $console_data;
    }

    public function setFileData(array $file_data)
    {
        $this->output['files'] = $file_data['files'];
        $this->output['fileTotals'] = $file_data['fileTotals'];
    }

    /**
     * Sets memory data
     *
     * @param array $data
     */
    public function setMemoryData(array $data)
    {
        $this->output['memory'] = array(
            'used'    => self::getReadableMemory($data['used']),
            'allowed' => $data['allowed']
        );
    }

    public function setQueryData(array $query_data)
    {
        // the void
    }

    /**
     * Sets speed data
     *
     * @param array $data
     */
    public function setSpeedData(array $data)
    {
        $this->output['speed'] = array(
            'elapsed' => self::getReadableTime($data['elapsed']),
            'allowed' => self::getReadableTime($data['allowed'], 0)
        );
    }

    /**
     * Static formatter for human-readable time
     * Only handles time up to 60 minutes gracefully
     *
     * @param double  $time
     * @param integer $decimals
     * @return string
     */
    public static function getReadableTime($time, $decimals = 3)
    {
        $unit = 's';
        if ($time < 1) {
            $time *= 1000;
            $unit = 'ms';
        } else if ($time > 60) {
            $time /= 60;
            $unit = 'm';
        }
        $time = number_format($time, $decimals);
        return "{$time} {$unit}";
    }

    /**
     * Static formatter for human-readable memory
     *
     * @param double  $size
     * @param integer $decimals
     */
    public static function getReadableMemory($size, $decimals = 2)
    {
        $unitOptions = array('b', 'k', 'M', 'G');

        $base = log($size, 1024);

        $memory = round(pow(1024, $base - floor($base)), $decimals);
        $unit = $unitOptions[floor($base)];
        return "{$memory} {$unit}";
    }
 
    public function __invoke()
    {
        $output = $this->output;
        require_once __DIR__ .'/../asset/display.tpl.php';
    }
}	
