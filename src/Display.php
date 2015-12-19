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
    protected $defaults = array(
        'script_path' => 'asset/script.js',
        'style_path'  => 'asset/style.css'
    );

    /** @var  array */
    protected $options;

    /** @var  array */
    protected $output;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $options = array_intersect_key($options, $this->defaults);
        $this->options = array_replace($this->defaults, $options);
    }

    public function setConsole(Console $console)
    {
        $console_data = array(
            'messages' => array(),
            'meta'    => array(
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
                        'message' => print_r($log['data'], true),
                        'type'    => 'log'
                    );
                    $console_data['meta']['log']++;
                    break;
                case 'memory':
                    $message = array(
                        'message' => (!empty($log['data_type']) ? "{$log['data_type']}: " : '') . $log['name'],
                        'data'    => $this->getReadableMemory($log['data']),
                        'type'    => 'memory'
                    );
                    $console_data['meta']['memory']++;
                    break;
                case 'error':
                    $message = array(
                        'message' => "Line {$log['line']}: {$log['data']} in {$log['file']}",
                        'type'    => 'error'
                    );
                    $console_data['meta']['error']++;
                    break;
                case 'speed':
                    $elapsedTime = $log['data'] - $this->startTime;
                    $message = array(
                        'message' => $log['name'],
                        'data'    => $this->getReadableTime($elapsedTime),
                        'type'    => 'speed'
                    );
                    $console_data['meta']['speed']++;
                    break;
                default:
                    $message = array(
                        'message' => "Unrecognized console log type: {$log['type']}",
                        'type'    => 'error'
                    );
                    $console_data['meta']['error']++;
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
            'messages' => array(),
            'meta'     => array(
                'count'   => count($data),
                'size'    => 0,
                'largest' => 0
            )
        );

        foreach ($data as $file) {
            array_push($fileData['messages'], array(
                'message' => $file['name'],
                'data'    => $this->getReadableMemory($file['size'])
            ));

            $fileData['meta']['size'] += $file['size'];
            if ($file['size'] > $fileData['meta']['largest']) {
                $fileData['meta']['largest'] = $file['size'];
            }
        }

        $fileData['meta']['size'] = $this->getReadableMemory($fileData['meta']['size']);
        $fileData['meta']['largest'] = $this->getReadableMemory($fileData['meta']['largest']);

        $this->output['files'] = $fileData;
    }

    /**
     * Sets memory data
     *
     * @param array $data
     */
    public function setMemoryData(array $data)
    {
        $this->output['memory']['meta'] = array(
            'used'    => $this->getReadableMemory($data['used']),
            'allowed' => $data['allowed']
        );
    }

    public function setQueryData(array $data)
    {
        $queryData = array(
            'messages' => array(),
            'meta'     => array(
                'count'   => count($data),
                'time'    => 0,
                'slowest' => 0
            )
        );

        foreach ($data as $query) {
            array_push($queryData['messages'], array(
                'message'  => $query['sql'],
                'sub_data' => array_filter($query['explain']),
                'data'     => $this->getReadableTime($query['time'])
            ));
            $queryData['meta']['time'] += $query['time'];
            if ($query['time'] > $queryData['meta']['slowest']) {
                $queryData['meta']['slowest'] = $query['time'];
            }
        }

        $queryData['meta']['time'] = $this->getReadableTime($queryData['meta']['time']);
        $queryData['meta']['slowest'] = $this->getReadableTime($queryData['meta']['slowest']);

        $this->output['query'] = $queryData;
    }

    /**
     * Sets speed data
     *
     * @param array $data
     */
    public function setSpeedData(array $data)
    {
        $this->output['speed']['meta'] = array(
            'elapsed' => $this->getReadableTime($data['elapsed']),
            'allowed' => $this->getReadableTime($data['allowed'], 0)
        );
    }

    /**
     * Formatter for human-readable time
     * Only handles time up to 60 minutes gracefully
     *
     * @param double  $time
     * @param integer $percision
     * @return string
     */
    protected function getReadableTime($time, $percision = 3)
    {
        $unit = 's';
        if ($time < 1) {
            $time *= 1000;
            $unit = 'ms';
        } else if ($time > 60) {
            $time /= 60;
            $unit = 'm';
        }
        $time = number_format($time, $percision);
        return "{$time} {$unit}";
    }

    /**
     * Formatter for human-readable memory
     * Only handles time up to a few gigs gracefully
     *
     * @param double  $size
     * @param integer $percision
     */
    protected function getReadableMemory($size, $percision = 2)
    {
        $unitOptions = array('b', 'k', 'M', 'G');

        $base = log($size, 1024);

        $memory = round(pow(1024, $base - floor($base)), $percision);
        $unit = $unitOptions[floor($base)];
        return "{$memory} {$unit}";
    }
 
    public function __invoke()
    {
        $output = $this->output;
        $header = array(
          'console' => count($output['console']['messages']),
          'speed'   => $output['speed']['meta']['elapsed'],
          'query'   => $output['query']['meta']['count'],
          'memory'  => $output['memory']['meta']['used'],
          'files'   => $output['files']['meta']['count']
        );

        $console = $output['console'];

        $speed = $output['speed'];
        $speed['messages'] = array_filter($console['messages'], function ($message) {
            return $message['type'] == 'speed';
        });

        $query = $output['query'];

        $memory = $output['memory'];
        $memory['messages'] = array_filter($console['messages'], function ($message) {
            return $message['type'] == 'memory';
        });

        $files = $output['files'];

        // todo is this really the best way to load these?
        $styles = file_get_contents(__DIR__ . "./../{$this->options['style_path']}");
        $script = file_get_contents(__DIR__ . "./../{$this->options['script_path']}");

        require_once __DIR__ .'/../asset/display.html';
    }
}	
