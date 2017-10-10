<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;
use App\Controller\Plugin\Score;
use App\Controller\Plugin\Layout;

class OrderController extends AbstractActionController
{
    public $districtId = null;
    public $customerId = null;

    public function init()
    {
        parent::init();

        $this->districtId = $_SESSION['customer_info']['district_id'];
        $this->customerId = $this->locator->get('Profile')['customer_id'];
    }

    public function indexAction()
    {
        $this->layout(Layout::LAYOUT_UE);
        $this->layout->title = '我的订单';

        $limit = array(0, 3);
        $where = array(
            'o.order_status = 1',
            sprintf("o.customer_id = %d", $this->customerId),
        );

        $status = trim($this->param('status'));
        switch ($status) {
            case 'picke': //待领取
                $where[] = "o.order_type IN ('pending', 'shipped')";
                break;
            case 'group': //组团中
                $where[] = "o.order_type IN ('group')";
                break;
            case 'review': //待评论
                $where[] = "o.order_type IN ('received')";
                break;
            case 'over': //已完成
                $where[] = "o.order_type IN ('review')";
                break;
            default:
                break;
        }

        if ($this->funcs->isAjax() && $this->param('type') == 'page') {
            $limit[0] = $this->param('pageSize') * $limit[1];
        }
        
        $files = "o.*, p.*";
        $sqlInfo = array(
            'setJoin' => 'LEFT JOIN t_products p ON p.product_id = o.product_id',
            'setWhere' => $where,
            'setLimit' => $limit,
            'setOrderBy' => 'o.order_id DESC',
        );

        $orderList = $this->models->order->getOrder($files, $sqlInfo);

        // print_r($orderList);die;
        return array(
            'orderList' => $orderList,
        );
    }

    public function createAction()
    {
        if (!$this->funcs->isAjax()) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        $id = trim($this->param('id'));
        $productInfo = $this->models->product->getProductById($id);
        if (!$productInfo) {
            return new JsonModel('error', '商品不存在');
        }
        if (!$productInfo['product_status']) {
            return new JsonModel('error', '商品已下线');
        }
        if (strtotime($productInfo['product_end']) < time()) {
            return new JsonModel('error', '已过商品领取时间');
        }
        if ($productInfo['order_num'] > 0) {
            return new JsonModel('error', '每人限领一次');
        }
        if ($productInfo['product_quantity'] < 1) {
            return new JsonModel('error', '商品已领完');
        }
        if ($this->locator->get('Profile')['customer_score'] < $productInfo['product_price']) {
            return new JsonModel('error', '你的账户积分不足');
        }
        $type = trim($this->param('type'));
        if (!in_array($type, array('self','logistics'))) {
            return new JsonModel('error', '非法订单，系统拒绝处理');
        }
        if ($type == 'logistics') { //配送
            if (!$productInfo['product_shaping_status']) {
                return new JsonModel('error', '商品不支持配送');
            }
            $addressId = $this->locator->get('Profile')['customer_default_address_id'];
            if (!$addressId) {
                return new JsonModel('error', '请填写你的收获地址');
            }

            $sql = "SELECT c.*, d.district_name
                    FROM t_customer_address c
                    LEFT JOIN t_district d ON d.district_id = c.district_id 
                    WHERE address_id = ?";
            $address = $this->locator->db->getRow($sql, $addressId);
            if (!$address) {
                return new JsonModel('error', '你的收获地址不存在');
            }
        } else { //自提
            $address = array(
                'user_name' => $this->locator->get('Profile')['customer_name'],
                'district_id' => $this->districtId,
                'district_name' => $this->locator->get('Profile')['city'],
                'user_address' => '',
                'user_tel' => '',
            );
        }

        $sql = "SELECT MAX(order_number) FROM t_orders";
        $maxCode = $this->locator->db->getOne($sql);
        if ($maxCode) {
            $last6 = substr($maxCode, -6);
            if (is_numeric($last6)) {
                $tmpStr = 1000000 + $last6 + 1;
                $newCode = date('YmdHis') . substr($tmpStr, -6);
            } else {
                $newCode = date('YmdHis'). '000001';
            }
        } else {
            $newCode = date('YmdHis') . "000001";
        }

        if ($productInfo['product_type'] == 2) { //组团
            //创建组团
            $sql = "INSERT INTO t_order_groups 
                    SET customer_id = :customer_id, 
                    product_id = :product_id";
            $status = $this->locator->db->exec($sql, array(
                'customer_id' => $this->customerId,
                'product_id' => $productInfo['product_id'],
            ));
            if (!$status) {
                return new JsonModel('error', '组团失败');
            }
            $groupId = $this->locator->db->lastInsertId();

            //创建订单
            $map = array(
                'order_number' => $newCode,
                'product_id' => $productInfo['product_id'],
                'product_name' => $productInfo['product_name'],
                'product_price' => $productInfo['product_price'],
                'customer_id' => $this->customerId,
                'shinging_type' => $type,
                'order_customer_name' => $address['user_name'],
                'district_id' => $address['district_id'],
                'district_name' => $address['district_name'],
                'order_address' => $address['user_address'],
                'order_tel' => $address['user_tel'],
            );
            $sql = "INSERT INTO t_orders 
                    SET order_number = :order_number,
                    product_id = :product_id,
                    product_name = :product_name,
                    product_quantity = 1,
                    product_price = :product_price,
                    customer_id = :customer_id,
                    shinging_type = :shinging_type,
                    order_customer_name = :order_customer_name,
                    district_id = :district_id,
                    district_name = :district_name,
                    order_address = :order_address,
                    order_type = 'group',
                    order_tel = :order_tel";
            $status = $this->locator->db->exec($sql, $map);
            if (!$status) {
                return new JsonModel('error', '订单创建失败');
            }

            return JsonModel::init('ok', '组团成功')->setRedirect($this->helpers->url('customer/group-detail', array('id' => $groupId)));
        } else { //白领
            //创建订单
            $map = array(
                'order_number' => $newCode,
                'product_id' => $productInfo['product_id'],
                'product_name' => $productInfo['product_name'],
                'product_price' => $productInfo['product_price'],
                'customer_id' => $this->customerId,
                'shinging_type' => $type,
                'order_customer_name' => $address['user_name'],
                'district_id' => $address['district_id'],
                'district_name' => $address['district_name'],
                'order_address' => $address['user_address'],
                'order_tel' => $address['user_tel'],
            );
            $sql = "INSERT INTO t_orders 
                    SET order_number = :order_number,
                    product_id = :product_id,
                    product_name = :product_name,
                    product_quantity = 1,
                    product_price = :product_price,
                    customer_id = :customer_id,
                    shinging_type = :shinging_type,
                    order_customer_name = :order_customer_name,
                    district_id = :district_id,
                    district_name = :district_name,
                    order_address = :order_address,
                    order_tel = :order_tel";
            $status = $this->locator->db->exec($sql, $map);
            if (!$status) {
                return new JsonModel('error', '订单创建失败');
            }

            //扣除积分
            $sql = "UPDATE t_customers 
                    SET customer_score = customer_score - ? 
                    WHERE customer_id = ?";
            $status = $this->locator->db->exec($sql, $map['product_price'], $this->customerId);
            if (!$status) {
                $sql = "DELETE FROM t_orders WHERE order_number = ?";
                $this->locator->db->exec($sql, $map['order_number']);
            }

            //记录积分变动
            $this->score(array(
                'type' => 'buy', 
                'des' => Score::GMSP, 
                'score' => $map['product_price'],
            ));
            
            //扣除商品数量
            $sql = "UPDATE t_products 
                    SET product_quantity = product_quantity - 1 
                    WHERE product_id = ?";
            $this->locator->db->exec($sql, $map['product_id']);

            return JsonModel::init('ok', '下单成功')->setRedirect($this->helpers->url('order/index'));
        }
    }

    public function qrcodeAction()
    {
        $this->layout->title = '领取二维码';

        $orderNum = trim($this->param('order_number'));
        
        $key = HTTP_SERVER . BASE_PATH . "order-{$orderNum}";
        
        return array(
            'key' => $key,
        );
    }

    public function infoAction()
    {
    	$orderNum = $this->param('key');
    	$where = sprintf("order_number = '%s'", $orderNum);
    	$info = $this->models->order->getOrderInfo($where);

    	print_r($info);die;
    }
}
