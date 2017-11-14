<?php

namespace App\Controller;

use Framework\Controller\AbstractActionController as ActionController;
use Framework\View\Model\ViewModel;
use Framework\Utils\Http;

/**
 * @property \Framework\View\HelperManager|\Hints\HelperManager $helpers The helper manager
 * @property \Framework\Model\ModelManager|\Hints\ModelManager $models The model manager
 *
 * @property Plugin\Funcs $funcs
 * @property Plugin\Perm $perm
 * @method Plugin\Log log($action, $data)
 */
abstract class AbstractActionController extends ActionController
{
    public function init()
    {
        // register helper functions
        $this->helpers
             ->register('funcs', function() {return $this->plugin('funcs');});

        // set default layout
        $this->layout();
        $this->layout
             ->addChild(new ViewModel('layout/includes/header'), '__header')
             ->addChild(new ViewModel('layout/includes/footer'), '__footer');

        //获取微信用户信息
        if (!isset($_SESSION['openid'])) {
            //邀请码
            $key = trim($this->param('key'));
            if ($key) {
                $_SESSION['key'] = $key;
            }

            if (false) {
                if ($this->helpers->controller() != 'wechat') {
                    if (!isset($_SESSION['redirect'])) {
                        $_SESSION['redirect'] = $this->helpers->selfUrl(null, false);
                    }
                    $this->funcs->redirect($this->helpers->url('wechat/index'));
                }
            } else {
                if ($this->param('openid')) {
                    $openid = trim($this->param('openid'));
                } else {
                   $openid = $this->funcs->randPassword(18); 
                }

                $districtInfo = $this->models->district->getDistrictInfo(array(sprintf("district_name LIKE'%s%%'", '上海')));

                $customerInfo = array(
                    'username' => '123',
                    'icon' => 'http://q.87.re/2015/08/201508101754256.jpg',
                    'lat' => 31.236176,
                    'lng' => 121.481689,
                    'city' => $districtInfo['district_name'],
                    'district_id' => $districtInfo['district_id'],
                );
                $_SESSION['openid'] = addslashes($openid);
                $_SESSION['customer_info'] = $customerInfo;

                $this->funcs->redirect($this->helpers->url('default/index'));
            }
        } elseif (!isset($_SESSION['customer_info']['lat'])) {
            if ($this->helpers->pageId() != 'wechat-latlng') {
                $this->funcs->redirect($this->helpers->url('wechat/latlng'));
            }
        } else {
            //判断是否是新用户
            $where = sprintf("customer_openid = '%s'", addslashes($_SESSION['openid']));
            $customer = $this->models->customer->getCustomerInfo($where);
            if (!$customer) {
                if ($this->helpers->pageId() != 'account-register') {
                    $this->funcs->redirect($this->helpers->url('account/register'));
                }
            } else {
                if (!isset($_SESSION['login'])) {
                    //记录登录日志
                    $sql = "INSERT INTO t_customer_login_log 
                            SET customer_id = :customer_id, 
                            district_id = :district_id, 
                            log_ip = :log_ip";
                    $this->locator->db->exec($sql, array(
                        'customer_id' => $customer['customer_id'],
                        'district_id' => $_SESSION['customer_info']['district_id'],
                        'log_ip' => Http::getIp(),
                    ));

                    $_SESSION['login'] = true;
                }

                $this->locator->setService('Profile', array_merge($customer, $_SESSION['customer_info']));

                if (isset($_SESSION['redirect'])) {
                    $redirect = $_SESSION['redirect'];
                    unset($_SESSION['redirect']);

                    $this->funcs->redirect($redirect);
                }
            }
        }
    }
}
