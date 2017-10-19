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
        if (!$this->funcs->isAjax()) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        $key = trim($_POST['key']);
        $orderNumber = $this->funcs->encrypt($key, 'D', QRCODE_KEY);
        $where = sprintf("order_number = '%s'", $id),;
        $info = $this->models->order->getOrderInfo($where);

        if (!$info) {
            return new JsonModel('error', '订单不存在');
        }
        if ($info['order_type'] != 'pending') {
            return new JsonModel('error', '商品已派送');
        }
        if ($info['shinging_type'] != 'self') {
            return new JsonModel('error', '商品非自提');
        }

        //获取订单商品
        $product = $this->models->product->getProductById($info['product_id']);
        if (!$product) {
            return new JsonModel('error', '商品不存在');
        }
        $time = strtotime($info['order_time']) + $product['product_qr_code_day'] * 86400 - time();
        if ($time < 0) {
            return new JsonModel('error', '二维码已过期');
        }

        $sql = "SELECT quantity_num 
                FROM t_product_quantity 
                WHERE product_id = ? 
                AND shop_id = ?";
        if ($this->locator->db->getOne($sql, $info['product_id'], $this->shopInfo['shop_id']) < 1) {
            return new JsonModel('error', '库存不足');
        }

        return JsonModel::init('ok', '', array(
            'href' => (string) $this->helpers->url('shop-admin/self-edit', array('id' => $info['order_id'])),
            'name' => $info['product_name'],
            'src' => (string) $this->helpers->uploadUrl($product['images']['banner'][0]['image_path'], 'product'),
        ));
    }

    public function selfEditAction()
    {
        if (!$this->funcs->isAjax()) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        $id = trim($this->param('id'));
        $where = array(
            sprintf("order_id = %d", $id),
            "order_type = 'pending'",
            "shinging_type = 'self'",
            "order_status = 1",
        );
        $info = $this->models->order->getOrderInfo($where);
        if (!$info) {
            return new JsonModel('error', '订单不存在，无法派送');
        }

        $sql = "UPDATE t_orders 
                SET order_type = 'shipped', 
                order_shipped_time = now(), 
                shop_id = :shop_id, 
                user_address = :user_address 
                WHERE order_id = ?";
        $status = $this->locator->db->exec($sql, array(
            'shop_id' => $this->shopInfo['shop_id'],
            'user_address' => $this->shopInfo['shop_address'],
        ));
        if (!$status) {
            return new JsonModel('error', '派送失败');
        }

        //修改库存
        $sql = "UPDATE t_product_quantity 
                SET quantity_num = quantity_num - 1 
                WHERE product_id = ? 
                AND shop_id = ?";
        $this->locator->db->exec($sql, $info['product_id'], $this->shopInfo['shop_id']);

        return new JsonModel('error', '派送成功');
    }

    public function logoutAction()
    {
        unset($_SESSION['login_id']);
        unset($_SESSION['login_name']);

        $this->funcs->redirect($this->helpers->url('shop/login'));
    }
}
