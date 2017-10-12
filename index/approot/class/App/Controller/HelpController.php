<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;

class HelpController extends AbstractActionController
{
    public $districtId = null;
    public $customerId = null;

    public function init()
    {
        parent::init();

        $this->districtId = $_SESSION['customer_info']['district_id'];
        $this->customerId = $this->locator->get('Profile')['customer_id'];
    }

    public function indexAction()
    {
        $this->layout->title = '帮助中心';

        return array();
    }

    public function feedbackAction()
    {
        $this->layout->title = '意见反馈';

        if ($this->funcs->isAjax()) {
            if (!$_POST['feedback_text']) {
                return new JsonModel('error', '请填写您宝贵的意见');
            }

            $sql = "INSERT INTO t_customer_feedbacks 
                    SET customer_id = ?, 
                    feedback_text = ?";
            $status = $this->locator->db->exec($sql, $this->customerId, trim($_POST['feedback_text']));
            if ($status) {
                return JsonModel::init('ok', '提交成功')->setRedirect($this->helpers->url('customer/index'));
            } else {
                return new JsonModel('error', '提交失败');
            }
        }

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
