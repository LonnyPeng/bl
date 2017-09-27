<?php

namespace App\Controller;

class AccountController extends AbstractActionController
{
    public function registerAction()
    {
        //自动注册新用户
        //保存头像
        $dir = USER_DIR;
        $upFileName = date('Ymd') . '/';
        $this->funcs->makeFile($dir . $upFileName);

        $ext = 'jpg';
        $fileName = md5($_SESSION['customer_info']['icon'] . $this->funcs->rand());
        for($i=0;; $i++) {
            if (file_exists($dir . $upFileName . $fileName . '.' . $ext)) {
                $fileName = md5($_SESSION['customer_info']['icon'] . $this->funcs->rand());
            } else {
                break;
            }
        }

        $uploadFile = $upFileName . $fileName . '.' . $ext;
        $uploadFilePath = $dir . $uploadFile;
        $img = file_get_contents($_SESSION['customer_info']['icon']);
        $handel = fopen($uploadFilePath, 'w');
        file_put_contents($uploadFilePath, $img);
        fclose($handel);

        //生成邀请码
        for($i=0;;$i++) {
            $code = strtoupper(substr($this->funcs->rand(), 0, 8));
            $where = sprintf("customer_invite_code = '%s'", $code);
            if (!$this->models->customer->getCustomerInfo($where)) {
                break;
            }
        }

        //判断是否被邀请的
        $inviteId = 0;
        if (isset($_SESSION['key'])) {
            $where = sprintf("customer_invite_code = '%s'", $_SESSION['key']);
            $info = $this->models->customer->getCustomerInfo($where);
            if ($info) {
                $inviteId = $info['customer_id'];
            }

            unset($_SESSION['key']);
        }

        $sql = "INSERT INTO t_customers 
                SET customer_openid = :customer_openid,
                customer_name = :customer_name,
                customer_headimg = :customer_headimg,
                customer_invite_code = :customer_invite_code,
                customer_invite_id = :customer_invite_id";
        $this->locator->db->exec($sql, array(
            'customer_openid' => $_SESSION['openid'],
            'customer_name' => $_SESSION['customer_info']['username'],
            'customer_headimg' => $uploadFile,
            'customer_invite_code' => $code,
            'customer_invite_id' => $inviteId,
        ));

        $this->funcs->redirect($this->helpers->url('default/index'));
    }
}
