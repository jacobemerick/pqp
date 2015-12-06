<?php

/* - - - - - - - - - - - - - - - - - - - - - - - - - - - 

 Title : HTML Output for Php Quick Profiler
 Author : Created by Ryan Campbell
 URL : http://particletree.com/features/php-quick-profiler/

 Last Updated : April 22, 2009

 Description : This is a horribly ugly function used to output
 the PQP HTML. This is great because it will just work in your project,
 but it is hard to maintain and read. See the README file for how to use
 the Smarty file we provided with PQP.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

namespace Particletree\Pqp;

class Display
{

    protected $console_data;

    public function __construct($console_data)
    {
        $this->console_data = $console_data;
    }

    public function __invoke($output)
    {
        require_once __DIR__ .'/../asset/display.tpl.php';
    }
}	
