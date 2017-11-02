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
        
        //获取code
        $urlInfo = array(
            'url' => "https://open.weixin.qq.com/connect/oauth2/authorize",
            'params' => array(
                'appid' => $this->app->config->app_id,
                'redirect_uri' => urlencode($this->helpers->url('wechat/subscribe', '', '', true)),
                'response_type' => 'code',
                'scope' => 'snsapi_base',
                'state' => 'STATE#wechat_redirect',
            ),
        );
        $this->funcs->redirect($this->funcs->urlInit($urlInfo));
        print_r($url);die;
        $this->funcs->curl($urlInfo);

        $oauth = $this->app->oauth;
        
        $oauth->redirect()->send();

        return false;
    }

    public function subscribeAction()
    {
        //获取openid
        $code = $this->param('code');
        $urlInfo = array(
            'url' => "https://api.weixin.qq.com/sns/oauth2/access_token",
            'params' => array(
                'appid' => $this->app->config->app_id,
                'secret' => $this->app->config->secret,
                'code' => $code,
                'grant_type' => 'authorization_code',
            ),
        );
        $result = $this->funcs->curl($urlInfo);
        $result = json_decode($result);
        $openid = $result->openid;

        //获取token
        $urlInfo = array(
            'url' => "https://api.weixin.qq.com/cgi-bin/token",
            'params' => array(
                'grant_type' => "client_credential",
                'appid' => $this->app->config->app_id,
                'secret' => $this->app->config->secret,
            ),
        );
        $result = curl($urlInfo);
        $result = json_decode($result);
        $token = $result->access_token;

        $urlInfo = array(
            'url' => "https://api.weixin.qq.com/cgi-bin/user/info",
            'params' => array(
                'access_token' => $token,
                'openid' => $openid,
                'lang' => "zh_CN",
            ),
        );
        $result = curl($urlInfo);
        $result = json_decode($result);
        //判断用户是否关注公众号
        if ($result->subscribe) {
            die('还没关注公众号');
        } else {
            $oauth = $this->app->oauth;
            
            $oauth->redirect()->send();
        }

        return false;
    }

    public function callbackAction()
    {
        $oauth = $this->app->oauth;

        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user();
        $userInfo = $user->toArray();
        $original = $userInfo['original'];

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

        $this->funcs->redirect($this->helpers->url('default/index'));
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
