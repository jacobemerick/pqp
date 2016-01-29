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
        'relative_path' => true,
        'script_path' => 'asset/script.js',
        'style_path' => 'asset/style.css'
    );

    /** @var  array */
    protected $options;

    /** @var  double */
    protected $startTime;

    /** @var  Console */
    protected $console;

    /** @var  array */
    protected $speedData;

    /** @var  array */
    protected $queryData;

    /** @var  array */
    protected $memoryData;

    /** @var  array */
    protected $fileData;

    /** @var  integer */
    protected $pathTrimStart = 0;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $options = array_intersect_key($options, $this->defaults);
        $this->options = array_replace($this->defaults, $options);

        if ($this->options['relative_path']) {
            $this->pathTrimStart = $this->getPathTrimStart(getcwd(), __DIR__);
        }
    }

    /**
     * @param double $startTime
     */
    public function setStartTime($startTime)
    {
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
     * Sets memory data
     *
     * @param array $data
     */
    public function setMemoryData(array $data)
    {
        $this->memoryData = $data;
    }

    /**
     * Sets query data
     *
     * @param array $data
     */
    public function setQueryData(array $data)
    {
        $this->queryData = $data;
    }

    /**
     * Sets speed data
     *
     * @param array $data
     */
    public function setSpeedData(array $data)
    {
        $this->speedData = $data;
    }

    /**
     * Sets file data
     *
     * @param array $data
     */
    public function setFileData(array $data)
    {
        $this->fileData = $data;
    }

    /**
     * @return integer
     */
    protected function getPathTrimStart($cwd, $dir)
    {
        for ($pos = 0; $pos <= strlen($cwd); $pos++) {
            if (strncmp($cwd, $dir, $pos + 1) !== 0) {
                break;
            }
        }

        return $pos;
    }

    /**
     * @return array
     */
    protected function getConsoleMeta()
    {
        $consoleMeta = array(
            'log' => 0,
            'memory' => 0,
            'error' => 0,
            'speed' => 0
        );
        foreach ($this->console->getLogs() as $log) {
            if (array_key_exists($log['type'], $consoleMeta)) {
                $consoleMeta[$log['type']]++;
                continue;
            }
            $consoleMeta['error']++;
        }

        return $consoleMeta;
    }

    /**
     * @return array
     */
    protected function getConsoleMessages()
    {
        $messages = array();
        foreach ($this->console->getLogs() as $log) {
            switch ($log['type']) {
                case 'log':
                    $message = array(
                        'message' => print_r($log['data'], true),
                        'type'    => 'log'
                    );
                    break;
                case 'memory':
                    $message = array(
                        'message' => (!empty($log['data_type']) ? "{$log['data_type']}: " : '') . $log['name'],
                        'data'    => $this->getReadableMemory($log['data']),
                        'type'    => 'memory'
                    );
                    break;
                case 'error':
                    $message = array(
                        'message' => "Line {$log['line']}: {$log['data']} in {$this->getFilePath($log['file'])}",
                        'type'    => 'error'
                    );
                    break;
                case 'speed':
                    $elapsedTime = $log['data'] - $this->startTime;
                    $message = array(
                        'message' => $log['name'],
                        'data'    => $this->getReadableTime($elapsedTime),
                        'type'    => 'speed'
                    );
                    break;
                default:
                    $message = array(
                        'message' => "Unrecognized console log type: {$log['type']}",
                        'type'    => 'error'
                    );
                    break;
            }
            array_push($messages, $message);
        }
        return $messages;
    }

    /**
     * @return array
     */
    protected function getSpeedMeta()
    {
        $elapsedTime = $this->getReadableTime($this->speedData['elapsed']);
        $allowedTime = $this->getReadableTime($this->speedData['allowed'], 0);

        return array(
            'elapsed' => $elapsedTime,
            'allowed' => $allowedTime,
        );
    }

    /**
     * @return array
     */
    public function getQueryMeta()
    {
        $queryCount = count($this->queryData);
        $queryTotalTime = array_reduce($this->queryData, function ($sum, $row) {
            return $sum + $row['time'];
        }, 0);
        $queryTotalTime = $this->getReadableTime($queryTotalTime);
        $querySlowestTime = array_reduce($this->queryData, function ($slowest, $row) {
            return ($slowest < $row['time']) ? $row['time'] : $slowest;
        }, 0);
        $querySlowestTime = $this->getReadableTime($querySlowestTime);

        return array(
            'count'   => $queryCount,
            'time'    => $queryTotalTime,
            'slowest' => $querySlowestTime
        );
    }

    /**
     * @return array
     */
    public function getQueryList()
    {
        $queryList = array();
        foreach ($this->queryData as $query) {
            array_push($queryList, array(
                'message'  => $query['sql'],
                'sub_data' => array_filter($query['explain']),
                'data'     => $this->getReadableTime($query['time'])
            ));
        }
        return $queryList;
    }

    /**
     * @return array
     */
    public function getMemoryMeta()
    {
        $usedMemory = $this->getReadableMemory($this->memoryData['used']);
        $allowedMemory = $this->memoryData['allowed']; // todo parse this, maybe?

        return array(
            'used'    => $usedMemory,
            'allowed' => $allowedMemory
        );
    }

    /**
     * @return array
     */
    protected function getFileMeta()
    {
        $fileCount = count($this->fileData);
        $fileTotalSize = array_reduce($this->fileData, function ($sum, $row) {
            return $sum + $row['size'];
        }, 0);
        $fileTotalSize = $this->getReadableMemory($fileTotalSize);
        $fileLargestSize = array_reduce($this->fileData, function ($largest, $row) {
            return ($largest < $row['size']) ? $row['size'] : $largest;
        }, 0);
        $fileLargestSize = $this->getReadableMemory($fileLargestSize);

        return array(
            'count' => $fileCount,
            'size' => $fileTotalSize,
            'largest' => $fileLargestSize
        );
    }

    /**
     * @return array
     */
    protected function getFileList()
    {
        $fileList = array();
        foreach ($this->fileData as $file) {
            array_push($fileList, array(
                'message' => $this->getFilePath($file['name']),
                'data'    => $this->getReadableMemory($file['size'])
            ));
        }
        return $fileList;
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
            $percision = 0;
            $unit = 'ms';
        } elseif ($time > 60) {
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

    /**
     * @param string $path
     * @return string
     */
    protected function getFilePath($path)
    {
        if (!$this->options['relative_path']) {
            return $path;
        }

        return substr($path, $this->pathTrimStart);
    }

    /**
     * @param array  $messages
     * @param string $type
     * @return array
     */
    protected function filterMessages($messages, $type)
    {
        return array_filter($messages, function ($message) use ($type) {
            return $message['type'] == $type;
        });
    }

    /**
     * @returns array
     */
    protected function gatherTemplateData()
    {
        $consoleMeta = $this->getConsoleMeta();
        $speedMeta = $this->getSpeedMeta();
        $queryMeta = $this->getQueryMeta();
        $memoryMeta = $this->getMemoryMeta();
        $fileMeta = $this->getFileMeta();

        $consoleMessages = $this->getConsoleMessages();
        $queryList = $this->getQueryList();
        $fileList = $this->getFileList();

        return array(
            'header' => array(
                'console' => array_sum($consoleMeta),
                'speed'   => $speedMeta['elapsed'],
                'query'   => $queryMeta['count'],
                'memory'  => $memoryMeta['used'],
                'files'   => $fileMeta['count']
            ),
            'console' => array(
                'meta' => $consoleMeta,
                'messages' => $consoleMessages
            ),
            'speed' => array(
                'meta' => $speedMeta,
                'messages' => $this->filterMessages($consoleMessages, 'speed')
            ),
            'query' => array(
                'meta' => $queryMeta,
                'messages' => $queryList
            ),
            'memory' => array(
                'meta' => $memoryMeta,
                'messages' => $this->filterMessages($consoleMessages, 'memory')
            ),
            'files' => array(
                'meta' => $fileMeta,
                'messages' => $fileList
            )
        );
    }

    public function __invoke()
    {
        $templateData = $this->gatherTemplateData();

        // todo is this really the best way to load these?
        $styles = file_get_contents(__DIR__ . "/../{$this->options['style_path']}");
        $script = file_get_contents(__DIR__ . "/../{$this->options['script_path']}");

        call_user_func(function () use ($templateData, $styles, $script) {
            extract($templateData);
            require_once __DIR__ . '/../asset/display.html';
        });
    }
}
