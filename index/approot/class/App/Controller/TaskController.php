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

        $sql = "SELECT * FROM t_turntable 
        		ORDER BY turntable_id DESC 
        		LIMIT 0,1";
        $config = $this->locator->db->getRow($sql);
        if ($config) {
        	$config['turntable_num'] = unserialize($config['turntable_num']);
        	$config['turntable_num'] = $config['turntable_num'][$this->locator->get('Profile')['level_id']];
        }

        print_r($config);die;
        
        return array();
    }
}
