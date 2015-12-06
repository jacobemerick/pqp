<?php

/*****************************************
 * Title : PHP Quick Profiler Class
 * Author : Created by Ryan Campbell
 * URL : http://particletree.com/features/php-quick-profiler/
 * Description : This class processes the logs and organizes the data
 * for output to the browser. Initialize this class with a start time at
 * the beginning of your code, and then call the display method when your
 * code is terminating.
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
       FORMAT THE DIFFERENT TYPES OF LOGS
  -------------------------------------------*/
  
  public function gatherConsoleData() {
    $console = array(
      'messages' => array(),
      'totals' => array(
        'log' => 0,
        'memory' => 0,
        'error' => 0,
        'speed' => 0
      ));
    $logs = $this->console->getLogs();
      foreach($logs as $log) {
        if($log['type'] == 'log') {
          $message = array(
            'data' => print_r($log['data'], true),
            'type' => 'log'
          );
          $console['totals']['log']++;
        }
        elseif($log['type'] == 'memory') {
          $message = array(
            'name'    => $log['name'],
            'data_type'    => $log['data_type'],
            'data'   => $this->getReadableFileSize($log['data']),
            'type' => 'memory'
          );
          $console['totals']['memory']++;
        }
        elseif($log['type'] == 'speed') {
          $message = array(
            'name' => $log['name'],
            'data'    => $this->getReadableTime(($log['data'] - $this->startTime) * 1000),
            'type' => 'speed'
          );
          $console['totals']['speed']++;
        } else {
          $message = array(
            'data' => $log['data'],
            'type' => 'error',
            'file' => $log['file'],
            'line' => $log['line']
          );
          $console['totals']['error']++;
        }
        array_push($console['messages'], $message);
      }
    $this->output['logs'] = array('console' => $console);
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

    foreach($files as $key => $file) {
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
    $this->output['files'] = $fileList;
    $this->output['fileTotals'] = $fileTotals;
  }
  
  /*-------------------------------------------
       MEMORY USAGE AND MEMORY AVAILABLE
  -------------------------------------------*/
  
  public function gatherMemoryData() {
    $memoryTotals = array();
    $memoryTotals['used'] = $this->getReadableFileSize(memory_get_peak_usage());
    $memoryTotals['total'] = ini_get("memory_limit");
    $this->output['memoryTotals'] = $memoryTotals;
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
      foreach($this->db->queries as $key => $query) {
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
  
  /*-------------------------------------------
       SPEED DATA FOR ENTIRE PAGE LOAD
  -------------------------------------------*/
  
  public function gatherSpeedData() {
    $speedTotals = array();
    $speedTotals['total'] = $this->getReadableTime((microtime(true) - $this->startTime)*1000);
    $speedTotals['allowed'] = ini_get("max_execution_time");
    $this->output['speedTotals'] = $speedTotals;
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
  
  public function getReadableTime($time) {
    $ret = $time;
    $formatter = 0;
    $formats = array('ms', 's', 'm');
    if($time >= 1000 && $time < 60000) {
      $formatter = 1;
      $ret = ($time / 1000);
    }
    if($time >= 60000) {
      $formatter = 2;
      $ret = ($time / 1000) / 60;
    }
    $ret = number_format($ret,3,'.','') . ' ' . $formats[$formatter];
    return $ret;
  }
  
  /*---------------------------------------------------------
       DISPLAY TO THE SCREEN -- CALL WHEN CODE TERMINATING
  -----------------------------------------------------------*/
  
  public function display($db = '', $master_db = '') {
    $this->db = $db;
    $this->master_db = $master_db;
    $this->gatherConsoleData();
    $this->gatherFileData();
    $this->gatherMemoryData();
    $this->gatherQueryData();
    $this->gatherSpeedData();
    require_once __DIR__ . '/../display.php';
    displayPqp($this->output);
  }
}
