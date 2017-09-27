<?php

namespace App\Controller;


class TaskController extends AbstractActionController
{
    public function init()
    {
        parent::init();
    }

    public function turntableAction()
    {
        $this->layout->title = '惊喜大转盘';
        
        return array();
    }
}
