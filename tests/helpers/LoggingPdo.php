<?php

namespace Particletree\Pqp;

use PDO;

class LoggingPdo extends PDO
{
    public $queries = array();
}
