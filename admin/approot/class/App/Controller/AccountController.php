<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;
use Framework\Utils\Http;

class AccountController extends AbstractActionController
{
    public function loginAction()
    {
        $this->layout(false);

        if ($this->funcs->isAjax()) {
            $memberName = trim($_POST['member_name']);
            $memberPasswd = trim($_POST['member_passwd']);

            $sql = "SELECT * FROM t_member WHERE member_name = ?";
            $member = $this->locator->db->getRow($sql, $memberName);
            if (!$member) {
                return new JsonModel('error', '用户名错误');
            }
            if (!$this->password->validate($memberPasswd, $member['member_password'])) {
                return new JsonModel('error', '密码错误');
            }
            if ($member['member_status'] < 1) {
                return new JsonModel('error', '用户已失效');
            }

            $_SESSION['login_id'] = intval($member['member_id']);
            $_SESSION['login_name'] = $member['member_name'];

            $sql = "SELECT perm_url, perm_value 
                    FROM t_perms 
                    WHERE member_id = ?";
            $permList = $this->locator->db->getPairs($sql, $member['member_id']);
            if ($permList) {
                foreach ($permList as $key => $value) {
                    $permList[$key] = $this->perm->getPerm($value);
                }
            }
            
            $_SESSION['member_perm'] = $permList;

            // record login time
            $sql = "UPDATE t_member
                    SET member_logtime = NOW(),
                        member_logip = ?,
                        member_lognum = member_lognum + 1
                    WHERE member_id = ?";
            $this->locator->db->exec($sql, Http::getIp(), $member['member_id']);

            // get redirect url
            $redirect = null;
            if (!empty($_POST['redirect'])) {
                $redirect = $_POST['redirect'];
                if (parse_url($redirect, PHP_URL_PATH) == $this->helpers->url('account/logout') || 
                    parse_url($redirect, PHP_URL_PATH) == $this->helpers->url('account/login')) {
                    $redirect = null;
                }
            }
            if (!$redirect) {
                $redirect = $this->helpers->url();
            }
            $model = new JsonModel('ok');
            $model->setRedirect($redirect);
            return $model;
        }

        return array();
    }

    public function logoutAction()
    {
        session_unset();
        session_destroy();
        $this->funcs->redirect($this->helpers->url('account/login'));
    }
}
