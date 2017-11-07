<?php

namespace App\Controller;

use EasyWeChat\Payment\Order;
use Framework\View\Model\JsonModel;
use EasyWeChat\Foundation\Application;

class WechatController extends AbstractActionController
{
    private $app = null;

    public function init()
    {
        parent::init();

        require_once VENDOR_DIR . 'autoload.php';

        $this->app = new Application(require_once CONFIG_DIR . 'wechat.config.php');
    }

    public function indexAction()
    {
        // 设置测试账号的菜单
        // $this->menu();
        // die;

        $oauth = $this->app->oauth;
        
        $oauth->redirect()->send();

        return false;
    }

    public function callbackAction()
    {
        $oauth = $this->app->oauth;

        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user();
        $userInfo = $user->toArray();
        $original = $userInfo['original'];
        if (!$original['city']) {
            $original['city'] = '上海市';
        }

        $districtInfo = $this->models->district->getDistrictInfo(array(sprintf("district_name LIKE'%s%%'", $original['city'])));
        if (!$districtInfo) {
            $sql = "SELECT d.district_name
                    FROM t_customers c 
                    LEFT JOIN t_district d ON d.district_id = c.district_id 
                    WHERE c.customer_openid = ?";
            $userCity = $this->locator->db->getOne($sql, $original['openid']);
            if ($userCity) {
                $original['city'] = $userCity;
            } elseif (!$original['city']) {
                $original['city'] = '上海市';
            }

            $districtInfo = $this->models->district->getDistrictInfo(array(sprintf("district_name LIKE'%s%%'", $original['city'])));
        }

        if (!preg_match("/^http:\/\/wx\.qlogo\.cn/i", $original['headimgurl'])) {
            $original['headimgurl'] = (string) $this->helpers->image('head_img.jpg', true);
        }

        $_SESSION['openid'] = $original['openid'];
        $_SESSION['customer_info'] = array(
            'username' => $original['nickname'],
            'icon' => $original['headimgurl'],
            'city' => $districtInfo['district_name'],
            'district_id' => $districtInfo['district_id'],
        );

        $this->funcs->redirect($this->helpers->url('wechat/latlng'));
    }

    public function latlngAction() 
    {
        if ($this->funcs->isAjax()) {
            $_SESSION['customer_info']['lat'] = $this->param('lat');
            $_SESSION['customer_info']['lng'] = $this->param('lng');

            return JsonModel::init('ok', '')->setRedirect($this->helpers->url('default/index'));
        }

        return array(
            'js' => $this->app->js,
        );
    }

    private function menu()
    {
        $menu = $this->app->menu;

        $urlPath = HTTP_SERVER . BASE_PATH;

        $buttons = [
            [
                "type" => "view",
                "name" => "商户登录",
                "url" => $urlPath . "shop/login",
            ],
            [
                "type" => "view",
                "name" => "我的白领",
                "url" => $urlPath . "default/index",
            ],
            [
                "type" => "view",
                "name" => "积分任务",
                "url" => $urlPath . "task/index",
            ],
        ];

        $menu->add($buttons);
    }
}
