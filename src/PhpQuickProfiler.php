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

    /** @var  Particletree\Pqp\Console */
    protected $console;

    /** @var  integer */
    protected $startTime;

    /**
     * @param Particletree\Pqp\Console $console
     * @param integer                  $startTime
     */
    public function __construct(Console $console, $startTime = null)
    {
        $this->console = $console;

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
  
  /*--------------------------------------------------------
       QUERY DATA -- DATABASE OBJECT WITH LOGGING REQUIRED
  ----------------------------------------------------------*/
  
  public function gatherQueryData() {
    $queryTotals = array();
    $queryTotals['count'] = 0;
    $queryTotals['time'] = 0;
    $queries = array();
    
    if($this->db != '') {
      $queryTotals['count'] += $this->db->queryCount;
      foreach($this->db->queries as $query) {
        $query = $this->attemptToExplainQuery($query);
        $queryTotals['time'] += $query['time'];
        $query['time'] = Display::getReadableTime($query['time']);
        $queries[] = $query;
      }
    }
    
    $queryTotals['time'] = Display::getReadableTime($queryTotals['time']);
    $this->output['queries'] = $queries;
    $this->output['queryTotals'] = $queryTotals;
  }
  
  /*--------------------------------------------------------
       CALL SQL EXPLAIN ON THE QUERY TO FIND MORE INFO
  ----------------------------------------------------------*/
  
  function attemptToExplainQuery($query) {
    try {
      $sql = 'EXPLAIN '.$query['sql'];
      $rs = $this->db->query($sql);
    }
    catch(Exception $e) {}
    if($rs) {
      $row = mysql_fetch_array($rs, MYSQL_ASSOC);
      $query['explain'] = $row;
    }
    return $query;
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
     */
    public function display(Display $display)
    {
        $display->setConsole($this->console);
        $display->setFileData($this->gatherFileData());
        $display->setMemoryData($this->gatherMemoryData());
        $display->setQueryData($this->gatherQueryData());
        $display->setSpeedData($this->gatherSpeedData());

        $display();
    }
}
