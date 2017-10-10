<?php

namespace App\Controller;

class HelpController extends AbstractActionController
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->layout->title = '帮助中心';

        return array();
    }

    public function feedbackAction()
    {
        $this->layout->title = '意见反馈';

        return array();
    }

    public function protocolAction()
    {
        $page = trim($this->param('status'));
        $sql = "SELECT * FROM t_configs WHERE config_page = ?";
        $info = $this->locator->db->getRow($sql, $page);

        $this->layout->title = $info['config_title'];

        return array(
            'info' => $info,
        );
    }
}
