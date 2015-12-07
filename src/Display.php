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

    public function setConsole(Console $console)
    {
        $console_data = array(
            'messages' => array(),
            'count'    => array(
                'log'    => 0,
                'memory' => 0,
                'error'  => 0,
                'speed'  => 0
            )
        );
        foreach ($console->getLogs() as $log) {
            switch($log['type']) {
                case 'log':
                    $message = array(
                        'data' => print_r($log['data'], true),
                        'type' => 'log'
                    );
                    $console_data['count']['log']++;
                    break;
                case 'memory':
                    $message = array(
                        'name' => $log['name'],
                        'data' => self::getReadableMemory($log['data']),
                        'type' => 'memory'
                    );
                    if (!empty($log['data_type'])) {
                        $message['data_type'] = $log['data_type'];
                    }
                    $console_data['count']['memory']++;
                    break;
                case 'error':
                    $message = array(
                        'data' => $log['data'],
                        'file' => $log['file'],
                        'line' => $log['line'],
                        'type' => 'error'
                    );
                    $console_data['count']['error']++;
                    break;
                case 'speed':
                    $elapsedTime = $log['data'] - $this->startTime;
                    $message = array(
                        'name' => $log['name'],
                        'data' => self::getReadableTime($elapsedTime),
                        'type' => 'speed'
                    );
                    $console_data['count']['speed']++;
                    break;
                default:
                    $message = array(
                        'data' => "Unrecognized console log type: {$log['type']}",
                        'type' => 'error'
                    );
                    $console_data['count']['error']++;
                    break;
            }
            array_push($console_data['messages'], $message);
        }
        $this->output['console'] = $console_data;
    }

    /**
     * Sets file data
     *
     * @param array $data
     */
    public function setFileData(array $data)
    {
        $fileData = array(
            'fileList'   => array(),
            'fileTotals' => array(
                'count'   => count($data),
                'size'    => 0,
                'largest' => 0
            )
        );

        foreach ($data as $file) {
            array_push($fileData['fileList'], array(
                'name' => $file['name'],
                'size' => self::getReadableMemory($file['size'])
            ));

            $fileData['fileTotals']['size'] += $file['size'];
            if ($file['size'] > $fileData['fileTotals']['largest']) {
                $fileData['fileTotals']['largest'] = $file['size'];
            }
        }

        $fileData['fileTotals']['size'] = self::getReadableMemory($fileData['fileTotals']['size']);
        $fileData['fileTotals']['largest'] = self::getReadableMemory($fileData['fileTotals']['largest']);

        $this->output['files'] = $fileData['fileList'];
        $this->output['fileTotals'] = $fileData['fileTotals'];
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

    public function setQueryData(array $data)
    {
        $queryData = array(
            'queries'     => array(),
            'queryTotals' => array(
                'count'   => count($data),
                'time'    => 0,
            )
        );

        foreach ($data as $query) {
            array_push($queryData['queries'], array(
                'sql'     => $query['sql'],
                'explain' => $query['explain'],
                'time'    => self::getReadableTime($query['time'])
            ));

            $queryData['queryTotals']['time'] += $query['time'];
        }

        $queryData['queryTotals']['time'] = self::getReadableTime($queryData['queryTotals']['time']);

        $this->output['queries'] = $queryData['queries'];
        $this->output['queryTotals'] = $queryData['queryTotals'];
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
