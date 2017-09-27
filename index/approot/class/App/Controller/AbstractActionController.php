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

                    for($i=0;;$i++) {
                        $code = strtoupper(substr($this->funcs->rand(), 0, 8));
                        $where = sprintf("customer_invite_code = '%s'", $code);
                        if (!$this->models->customer->getCustomerInfo($where)) {
                            break;
                        }
                    }

                    $sql = "INSERT INTO t_customers 
                            SET customer_openid = :customer_openid,
                            customer_name = :customer_name,
                            customer_headimg = :customer_headimg,
                            customer_invite_code = :customer_invite_code";
                    $this->locator->db->exec($sql, array(
                        'customer_openid' => $_SESSION['openid'],
                        'customer_name' => $_SESSION['customer_info']['username'],
                        'customer_headimg' => $uploadFile,
                        'customer_invite_code' => $code,
                    ));

                    $customer = $this->models->customer->getCustomerInfo($where);
                }

                $this->locator->setService('Profile', array_merge($customer, $_SESSION['customer_info']));
            }
        }
    }
}
