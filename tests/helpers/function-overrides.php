<?php

namespace Particletree\Pqp;

// namespace hack on microtime functionality
function microtime()
{
    return 1450355136.5706;
}

// namespace hack on included files functionality
function get_included_files()
{
    return array(
        'index.php',
        'src/Class.php'
    );
}

// namespace hack on filesize
function filesize($filename)
{
    return strlen($filename) * 100;
}

// namespace hack on memory usage
function memory_get_usage()
{
    return 12345678;
}

// namespace hack on memory usage
function memory_get_peak_usage()
{
    return 123456789;
}

// namespace hack on ini settings
function ini_get($setting)
{
    if ($setting == 'memory_limit') {
        return '128M';
    } elseif ($setting == 'max_execution_time') {
        return '30';
    }
    return \ini_get($setting);
}
