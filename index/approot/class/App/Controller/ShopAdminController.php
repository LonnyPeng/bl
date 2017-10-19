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

        //商品领用记录
        $where = array(
            sprintf("o.shop_id = %d", $this->shopInfo['shop_id']),
            "o.order_type = 'shipped'",
        );
        $limit = array(0, 10);
        $list = $this->list($where, $limit);

        //近30天派送
        $where['start'] = sprintf("o.order_shipped_time > '%s'", date("Y-m-d 00:00:00", strtotime("- 30 days")));
        $where['end'] = sprintf("o.order_shipped_time <= '%s'", date("Y-m-d H:i:s"));
        $count_30 = count($this->list($where));

        //近7天派送
        $where['start'] = sprintf("o.order_shipped_time > '%s'", date("Y-m-d 00:00:00", strtotime("- 7 days")));
        $where['end'] = sprintf("o.order_shipped_time <= '%s'", date("Y-m-d H:i:s"));
        $count_7 = count($this->list($where));

        //昨天派送
        $where['start'] = sprintf("o.order_shipped_time > '%s'", date("Y-m-d 00:00:00", strtotime("- 1 days")));
        $where['end'] = sprintf("o.order_shipped_time <= '%s'", date("Y-m-d 23:59:59", strtotime("- 1 days")));
        $count_1 = count($this->list($where));

        // print_r($where);die;
        return array(
            'shopInfo' => $this->shopInfo,
            'list' => $list,
            'count_30' => $count_30,
            'count_7' => $count_7,
            'count_1' => $count_1,
        );
    }

    public function listAction()
    {
        $this->layout->title = '领用数据';

        $where = array(
            sprintf("o.shop_id = %d", $this->shopInfo['shop_id']),
            "o.order_type = 'shipped'",
        );

        if ($this->param('statr')) {
            $where['start'] = sprintf("o.order_shipped_time > '%s'", date("Y-m-d 00:00:00", strtotime(trim($this->param('statr')))));
        }
        if ($this->param('end')) {
            $where['start'] = sprintf("o.order_shipped_time <= '%s'", date("Y-m-d 23:59:59", strtotime(trim($this->param('end')))));
        }

        $limit = array(0, 10);
        $list = $this->list($where, $limit);

        return array(
            'list' => $list,
        );
    }

    protected function list($where = array(), $limit = array())
    {
        $sql = "SELECT o.*, c.customer_name FROM t_orders o
                LEFT JOIN t_customers c ON c.customer_id = o.customer_id";

        if (is_array($where) && count($where) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        } elseif (is_string($where) && '' != $where) {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= " ORDER BY o.order_id DESC";
        if (is_array($limit) && count($limit) > 0 && count($limit) < 3) {
            $sql .= ' LIMIT ' . implode(',', $limit);
        } elseif (is_string($limit) && '' != $limit) {
            $sql .= ' LIMIT ' . $limit;
        }

        // print_r($sql);die;
        return (array) $this->locator->db->getAll($sql);
    }

    public function productListAction()
    {
        $this->layout->title = '商品管理';

        if ($this->funcs->isAjax()) { //补货
            $productId = trim($this->param('id'));
            $sql = "SELECT COUNT(*) 
                    FROM t_product_refill 
                    WHERE product_id = ? 
                    AND shop_id = ?";
            if ($this->locator->db->getOne($sql, $productId, $this->shopInfo['shop_id'])) {
                return new JsonModel('error', '你已提交，请等待');
            }

            $sql = "INSERT INTO t_product_refill 
                    SET product_id = ?, 
                    shop_id = ?";
            $status = $this->locator->db->exec($sql, $productId, $this->shopInfo['shop_id']);
            if ($status) {
                return JsonModel::init('ok', '提交成功');
            } else {
                return JsonModel::init('error', '提交失败');
            }
        }

        //获取库存商品
        $sql = "SELECT * FROM t_product_quantity  pq WHERE pq.shop_id = ?";
        $list = (array) $this->locator->db->getAll($sql, $this->shopInfo['shop_id']);
        foreach ($list as $key => $row) {
            $list[$key]['product'] = $this->models->product->getProductById($row['product_id']);
        }

        return array(
            'list' => $list,
        );
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
        $where = sprintf("order_number = '%s'", $orderNumber);
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
                WHERE order_id = :order_id";
        $status = $this->locator->db->exec($sql, array(
            'shop_id' => $this->shopInfo['shop_id'],
            'user_address' => $this->shopInfo['shop_address'],
            'order_id' => $id,
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

        return JsonModel::init('error', '派送成功')->setRedirect($this->helpers->url('shop-admin/index'));
    }

    public function passwordForgetAction()
    {
        if ($this->funcs->isAjax()) {
            $passwordOld = trim($_POST['suser_password_old']);
            if (!$passwordOld) {
                return new JsonModel('error', '输入原始密码');
            }

            if (!$this->password->validate($passwordOld, $this->shopInfo['suser_password'])) {
                return new JsonModel('error', '密码错误');
            }

            $password = trim($_POST['suser_password']);
            if ($password != trim($_POST['suser_password_r'])) {
                return new JsonModel('error', '两次密码不一样');
            }

            $sql = "UPDATE t_shop_users SET suser_password = ? WHERE suser_id = ?";
            $status = $this->locator->db->exec($sql, $this->password->encrypt($password), $this->shopInfo['suser_id']);
            if ($status) {
                return JsonModel::init('ok', '修改成功')->setRedirect($this->helpers->url('shop-admin/logout'));
            } else {
                return new JsonModel('error', '修改失败');
            }
        }

        return array();
    }

    public function logoutAction()
    {
        unset($_SESSION['login_id']);
        unset($_SESSION['login_name']);

        $this->funcs->redirect($this->helpers->url('shop/login'));
    }

    public function setUpAction()
    {
        $this->layout->title = "设置";

        return array();
    }
}
