<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;

class CustomerController extends AbstractActionController
{
    public function init()
    {
        parent::init();
    }

    public function qrcodeAction()
    {
        $openId = '123';
        $key = HTTP_SERVER . BASE_PATH . "customer/invite?key=";

        if ($this->funcs->isAjax()) {
            for($i=0;;$i++) {
                $code = strtoupper(substr($this->funcs->rand(), 0, 8));
                $where = sprintf("customer_invite_code = '%s'", $code);
                if (!$this->models->customer->getCustomerInfo($where)) {
                    break;
                }
            }

            $sql = "UPDATE t_customers SET customer_invite_code = ? WHERE customer_openid = ?";
            $status = $this->locator->db->exec($sql, $code, $openId);
            if ($status) {
                $key .= $code;
                $src = (string) $this->helpers->url('common/qrcode', array('key' => $this->funcs->encrypt($key, 'E', QRCODE_KEY)));
                return JsonModel::init('ok', '', array('src' => $src));
            } else {
                return new JsonModel('error', '刷新失败');
            }
        }

        $where = sprintf("customer_openid = '%s'", $openId);
    	$info = $this->models->customer->getCustomerInfo($where);

        $key .= $info['customer_invite_code'];

    	return array(
    		'key' => $key,
    	);
    }

    public function inviteAction()
    {
        $inviteCode = $this->param('key');
        $where = sprintf("customer_invite_code = '%s'", $inviteCode);
        $info = $this->models->customer->getCustomerInfo($where);

        print_r($info);die;
    }
}
