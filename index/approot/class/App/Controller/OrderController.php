<?php

namespace App\Controller;

use Framework\View\Model\ViewModel;
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
        
        $sqlInfo = array(
            'setWhere' => $where,
            'setLimit' => $limit,
            'setOrderBy' => 'o.order_id DESC',
        );

        $orderList = $this->models->order->getOrder('*', $sqlInfo);
        if ($orderList) {
            foreach ($orderList as $key => $row) {
                $orderList[$key]['product'] = $this->models->product->getProductById($row['product_id']);
                if ($row['order_type'] == 'pending') {
                    if ($row['shinging_type'] == 'logistics') {
                        $orderList[$key]['status'] = '待配送';
                    } else {
                        $orderList[$key]['status'] = '待自提';
                    }
                } elseif ($row['order_type'] == 'shipped') {
                    $orderList[$key]['status'] = '待领取';
                } elseif ($row['order_type'] == 'group') {
                    $sql = "SELECT group_id FROM t_order_groups WHERE customer_id IN (?) AND product_id = ?";
                    $groupId = $this->locator->db->getOne($sql, $this->customerId, $row['product_id']);
                    $result = $this->models->orderGroup->getOrderGroupById($groupId);
                    $orderList[$key]['status'] = $result['group_type']['msg'];
                    $orderList[$key]['group_id'] = $groupId;
                } elseif ($row['order_type'] == 'received') {
                    $orderList[$key]['status'] = '已收货';
                } else {
                    $orderList[$key]['status'] = '已完成';
                }
            }
        }

        if ($this->funcs->isAjax() && $this->param('type') != 'page') {
            //确认收货
            $id = trim($this->param('id'));
            $sql = "UPDATE t_orders 
                    SET order_type = 'received', 
                    order_received_time = now() 
                    WHERE order_id = ? 
                    AND order_type = 'shipped'
                    AND order_status = 1";
            $status = $this->locator->db->exec($sql, $id);
            if ($status) {
                return JsonModel::init('ok', '收货成功')->setRedirect($this->helpers->url('order/index', array('status' => 'review')));
            } else {
                return new JsonModel('error', '收货失败');
            }
        }

        if ($this->funcs->isAjax() && $this->param('type') == 'page') {
            if ($orderList) {
                $viewModel = new ViewModel(array('orderList' => $orderList), 'order/row');
                return JsonModel::init('ok', '', array('list' => $this->view->render($viewModel)));
            } else {
                return new JsonModel('error', '');
            }
        } else {
            return array(
                'orderList' => $orderList,
            );
        }
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
                'user_tel' => $this->locator->get('Profile')['customer_tel'],
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
            if ($this->param('group_id')) { //被邀请组团
                $groupId = trim($this->param('group_id'));
                $sql = "SELECT customer_id FROM t_order_groups WHERE group_id = ?";
                $customers = $this->locator->db->getOne($sql, $groupId);
                if (!$customers) {
                    return new JsonModel('error', '成团不存在');
                }
                if (in_array($this->customerId, explode(",", $customers))) {
                    return new JsonModel('error', '你已加入该团');
                }

                $customers .= "," . $this->customerId;
                $sql = "UPDATE t_order_groups SET customer_id = ? WHERE group_id = ?";
                $status = $this->locator->db->exec($sql, $customers, $groupId);
                if (!$status) {
                    return new JsonModel('error', '组团失败');
                }
            } else { //创建组团
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
            }

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

            //判断是否是被邀请的用户第一次下单，非组团的
            if ($this->locator->get('Profile')['customer_invite_id']) {
                //下单次数
                $sql = "SELECT COUNT(*) FROM t_orders WHERE customer_id = ?";
                $orderCount = $this->locator->db->getOne($sql, $this->customerId);
                if (!$orderCount) {
                    //给邀请人50积分奖励
                    $sql = "UPDATE t_customers 
                            SET customer_score = customer_score + :customer_score 
                            WHERE customer_id = :customer_id";
                    $this->locator->db->exec($sql, array(
                        'customer_score' => '50',
                        'customer_id' => $this->locator->get('Profile')['customer_invite_id'],
                    ));

                    //记录积分日志
                    $sql = "INSERT INTO t_customer_score_log 
                            SET customer_id = :customer_id,
                            score_type = :score_type,
                            score_des = :score_des,
                            score_quantity = :score_quantity";
                    $this->locator->db->exec($sql, array(
                        'customer_id' => $this->locator->get('Profile')['customer_invite_id'],
                        'score_type' => 'have',
                        'score_des' => Score::YQHY,
                        'score_quantity' => '50',
                    ));
                }
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
        $where = sprintf("order_number = '%s'", $orderNum);
        $info = $this->models->order->getOrderInfo($where);
        if (!$info) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }
        $sql = "SELECT product_qr_code_day FROM t_products WHERE product_id = ?";
        $qrcodeDay = $this->locator->db->getOne($sql, $info['product_id']);
        $time = strtotime($info['order_time']) + $qrcodeDay * 86400 - time();
        
        $key = HTTP_SERVER . '/admin/' . "order/self?key=" . $orderNum;
        
        return array(
            'key' => $key,
            'time' => $time,
        );
    }

    public function infoAction()
    {
    	$id = $this->param('id');
    	$where = sprintf("order_id = '%s'", $id);
    	$info = $this->models->order->getOrderInfo($where);
        if (!$info) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        $info['product'] = $this->models->product->getProductById($info['product_id']);
        if ($info['order_type'] == 'pending') {
            if ($info['shinging_type'] == 'logistics') {
                $info['status'] = '待配送';
            } else {
                $info['status'] = '待自提';
            }
        } elseif ($info['order_type'] == 'shipped') {
            $info['status'] = '待领取';
        } elseif ($info['order_type'] == 'group') {
            $sql = "SELECT group_id FROM t_order_groups WHERE customer_id IN (?) AND product_id = ?";
            $groupId = $this->locator->db->getOne($sql, $this->customerId, $info['product_id']);
            $result = $this->models->orderGroup->getOrderGroupById($groupId);
            $info['status'] = $result['msg'];
        } elseif ($info['order_type'] == 'received') {
            $info['status'] = '已收货';
        } else {
            $info['status'] = '已完成';
        }

        $this->layout->title = '我的订单';

        $sql = "SELECT product_qr_code_day FROM t_products WHERE product_id = ?";
        $qrcodeDay = $this->locator->db->getOne($sql, $info['product_id']);
        $time = strtotime($info['order_time']) + $qrcodeDay * 86400 - time();

        $key = HTTP_SERVER . '/admin/' . "order/self?key=" . $info['order_number'];

    	// print_r($key);die;
        return array(
            'info' => $info,
            'key' => $key,
            'time' => $time,
        );
    }

    public function selfAddressAction()
    {
        if (!$this->funcs->isAjax()) {
            return false;
        }

        $orderId = trim($this->param('order_id'));
        $where = sprintf("order_id = %d", $orderId);
        $info = $this->models->order->getOrderInfo($where);
        if (!$info) {
            return new JsonModel('error', '订单不存在');
        }
        if ($info['shinging_type'] != 'self') {
            return new JsonModel('error', '订单不是自提');
        }
        if ($info['order_type'] != 'pending') {
            return new JsonModel('error', '订单状态错误');
        }

        //库存
        $quantityId = trim($this->param('quantity_id'));
        $sql = "SELECT pq.*, s.shop_address
                FROM t_product_quantity pq 
                LEFT JOIN t_shops s ON pq.shop_id = s.shop_id
                WHERE quantity_id = ?";
        $quantityInfo = $this->locator->db->getRow($sql, $quantityId);
        if ($quantityInfo['quantity_num'] < 1) {
            return new JsonModel('error', '库存不足');
        }

        //修改库存
        $sql = "UPDATE t_product_quantity 
                SET quantity_num = quantity_num - 1 
                WHERE quantity_id";
        $this->locator->db->exec($sql, $quantityId);

        //修改订单状态
        $sql = "UPDATE t_orders 
                SET order_type = 'shipped', 
                order_address = ? 
                WHERE order_id = ?";
        $this->locator->db->exec($sql, $quantityInfo['shop_address'], $orderId);

        return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('order/info', array('id' => $orderId)));
    }
}
