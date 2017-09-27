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
                    $this->funcs->redirect($this->helpers->url('wechat/index'));
                }
            } else {
                if ($this->param('openid')) {
                    $openid = trim($this->param('openid'));
                } else {
                   $openid = $this->funcs->randPassword(18); 
                }

                $customerInfo = array(
                    'username' => '123',
                    'icon' => 'http://q.87.re/2015/08/201508101754256.jpg',
                    'lat' => 31.236176,
                    'lng' => 121.481689,
                );
                $_SESSION['openid'] = addslashes($openid);
                $_SESSION['customer_info'] = $customerInfo;

                $this->funcs->redirect($this->helpers->url('default/index'));
            }
        } else {
            //获取用户位置
            if (!isset($_SESSION['customer_info']['lat'])) {
                if ($this->helpers->pageId() != 'wechat-latlng') {
                    if (!isset($_SESSION['get_latlng'])) {
                        $_SESSION['get_latlng'] = 0;
                    }
                    
                    if ($_SESSION['get_latlng'] < 1) {
                        $this->funcs->redirect($this->helpers->url('wechat/latlng'));
                    } else {
                        $_SESSION['customer_info']['lat'] = 31.236176;
                        $_SESSION['customer_info']['lng'] = 121.481689;
                        $this->funcs->redirect($this->helpers->url('default/index'));
                    } 
                }
            } else {
                //判断是否是新用户
                $where = sprintf("customer_openid = '%s'", $_SESSION['openid']);
                $customer = $this->models->customer->getCustomerInfo($where);
                if (!$customer) {
                    if ($this->helpers->pageId() != 'account-register') {
                        $this->funcs->redirect($this->helpers->url('account/register'));
                    }
                } else {
                    $this->locator->setService('Profile', array_merge($customer, $_SESSION['customer_info']));
                }
            }
        }
    }
}
