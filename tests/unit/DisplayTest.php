<?php

namespace Particletree\Pqp;

use PHPUnit_Framework_TestCase;

class DisplayTest extends PHPUnit_Framework_TestCase
{

    public function testGetReadableTime()
    {
        $test_input = array(
            '.032432' => '32.432 ms',
            '24.3781' => '24.378 s',
            '145.123' => '2.419 m'
        );

        foreach ($test_input as $input => $expected_return) {
            $this->assertEquals($expected_return, Display::getReadableTime($input));
        }
    }

    public function testGetReadableMemory()
    {
        $test_input = array(
            '314'     => '314 b',
            '7403'    => '7.23 k',
            '2589983' => '2.47 M'
        );

        foreach ($test_input as $input => $expected_return) {
            $this->assertEquals($expected_return, Display::getReadableMemory($input));
        }
    }
}
