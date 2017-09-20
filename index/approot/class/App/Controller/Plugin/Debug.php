<?php

namespace App\Controller\Plugin;

class Debug
{
    public function ajaxPost()
    {
        error_reporting(E_ALL & ~E_NOTICE);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_REQUEST = $_POST = $_GET;
    }
}