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
        //设置测试账号的菜单
        $this->menu();
        die;

        $oauth = $this->app->oauth;
        
        $oauth->redirect()->send();
    }

    public function callbackAction()
    {
        $oauth = $this->app->oauth;

        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user();
        $userInfo = $user->toArray();
        $original = $userInfo['original'];

        $districtInfo = $this->models->district->getDistrictInfo(array(sprintf("district_name LIKE'%s%%'", $original['city'])));

        $_SESSION['openid'] = $original['openid'];
        $_SESSION['customer_info'] = array(
            'username' => $original['nickname'],
            'icon' => $original['headimgurl'],
            'city' => $districtInfo['district_name'],
            'district_id' => $districtInfo['district_id'],
        );

        $this->funcs->redirect($this->helpers->url('default/index'));
    }

    public function latlngAction()
    {
        $_SESSION['get_latlng']++;

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
                "name" => "粉丝福利",
                "url" => $urlPath . "default/index",
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
