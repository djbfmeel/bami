<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once 'RestInterface.php';

/**
 * Api
 *
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 */
class Api extends RestInterface
{
    public function __construct($request)
    {
        parent::__construct($request);
    }

    /**
     * @return string
     */
    protected function test()
    {
        if ($this->method == 'GET') {
            return "Success";
        } else {
            return "Only accepts GET requests";
        }
    }

}
