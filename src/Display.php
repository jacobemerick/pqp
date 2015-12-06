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

    public function setMemoryData(array $memory_data)
    {
        $this->output['memoryTotals'] = $memory_data;
    }

    public function setQueryData(array $query_data)
    {
        // the void
    }

    public function setSpeedData(array $speed_data)
    {
        $this->output['speedTotals'] = $speed_data;
    }

    public function __invoke()
    {
        $output = $this->output;
        require_once __DIR__ .'/../asset/display.tpl.php';
    }
}	
