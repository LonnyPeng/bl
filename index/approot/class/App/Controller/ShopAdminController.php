<?php

namespace App\Controller;

use EasyWeChat\Foundation\Application;
use Framework\View\Model\JsonModel;
use Framework\Utils\Http;

class ShopAdminController extends AbstractActionController
{
    private $shopInfo = null;
    public $app = null;

    public function init()
    {
        parent::init();

        if (!isset($_SESSION['shop_login_id']) || !isset($_SESSION['shop_login_name'])) {
            $this->funcs->redirect($this->helpers->url('shop/login', array(
                'redirect' => $this->helpers->selfUrl(null, false)
            )));
        } else {
            $sql = "SELECT * FROM t_shop_users su 
                    LEFT JOIN t_shops s ON s.shop_id = su.shop_id
                    WHERE su.suser_id = ? AND su.suser_name = ?";
            $shopInfo = $this->locator->db->getRow($sql, $_SESSION['shop_login_id'], $_SESSION['shop_login_name']);
            if (!$shopInfo) {
                Http::headerStatus(403);
            }

            $this->shopInfo = $shopInfo;
        }
    }

    public function indexAction()
    {
        $this->layout->title = '商家主页';


        print_r($this->shopInfo);die;
        return array();
    }

    public function scanQrcodeAction()
    {
        require_once VENDOR_DIR . 'autoload.php';

        $this->app = new Application(require_once CONFIG_DIR . 'wechat.config.php');

        $this->layout->title = '扫一扫';

        return array(
            'js' => $this->app->js,
        );
    }

    public function selfAction()
    {
        require_once VENDOR_DIR . 'autoload.php';

        $this->app = new Application(require_once CONFIG_DIR . 'wechat.config.php');

        $this->layout->title = '扫一扫';

        // $orderNumber = trim($this->param('key'));
        // $info = $this->models->order->getOrderByNumber($orderNumber);

        // print_r($info);die;
        return array(
            'js' => $this->app->js,
        );
    }

    public function logoutAction()
    {
        unset($_SESSION['login_id']);
        unset($_SESSION['login_name']);

        $this->funcs->redirect($this->helpers->url('shop/login'));
    }
}
