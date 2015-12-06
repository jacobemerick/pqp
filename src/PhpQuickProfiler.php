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
 
  /*-------------------------------------------
      AGGREGATE DATA ON THE FILES INCLUDED
  -------------------------------------------*/
  
  public function gatherFileData() {
    $files = get_included_files();
    $fileList = array();
    $fileTotals = array(
      "count" => count($files),
      "size" => 0,
      "largest" => 0,
    );

    foreach($files as $file) {
      $size = filesize($file);
      $fileList[] = array(
          'name' => $file,
          'size' => $this->getReadableFileSize($size)
        );
      $fileTotals['size'] += $size;
      if($size > $fileTotals['largest']) $fileTotals['largest'] = $size;
    }
    
    $fileTotals['size'] = $this->getReadableFileSize($fileTotals['size']);
    $fileTotals['largest'] = $this->getReadableFileSize($fileTotals['largest']);

    return array(
      'files' => $fileList,
      'fileTotals' => $fileTotals
    );
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
        $query['time'] = $this->getReadableTime($query['time']);
        $queries[] = $query;
      }
    }
    
    $queryTotals['time'] = $this->getReadableTime($queryTotals['time']);
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
  
  /*-------------------------------------------
       HELPER FUNCTIONS TO FORMAT DATA
  -------------------------------------------*/
  
  public function getReadableFileSize($size, $retstring = null) {
          // adapted from code at http://aidanlister.com/repos/v/function.size_readable.php
         $sizes = array('bytes', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

         if ($retstring === null) { $retstring = '%01.2f %s'; }

    $lastsizestring = end($sizes);

    foreach ($sizes as $sizestring) {
          if ($size < 1024) { break; }
             if ($sizestring != $lastsizestring) { $size /= 1024; }
         }
         if ($sizestring == $sizes[0]) { $retstring = '%01d %s'; } // Bytes aren't normally fractional
         return sprintf($retstring, $size, $sizestring);
  }

    /**
     * Static formatter for human-readable time
     * Only handles time up to 60 minutes gracefully
     *
     * @param integer $time time in seconds
     * @return string
     */
    public static function getReadableTime($time)
    {
        $unit = 's';

        if ($time < 1) {
            $time *= 1000;
            $unit = 'ms';
        } else if ($time > 60) {
            $time /= 60;
            $unit = 'm';
        }

        $time = number_format($time, 3);
        return "{$time} {$unit}";
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
