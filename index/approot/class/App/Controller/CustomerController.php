<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;
use App\Controller\Plugin\Layout;

class CustomerController extends AbstractActionController
{
    public $districtId = null;
    public $customerId = null;
    public $levelColor = array('1' => '#fff', '2' => '#ff7b00', '3' => '#f03c3c', '4' => '#cf9911');

    public function init()
    {
        parent::init();

        $this->districtId = $_SESSION['customer_info']['district_id'];
        $this->customerId = $this->locator->get('Profile')['customer_id'];
    }

    public function indexAction()
    {
        $this->layout(Layout::LAYOUT_UE);
        $this->layout->title = '个人中心';

        //待领取
        $sql = "SELECT COUNT(*) FROM t_orders 
                WHERE order_status = 1 
                AND order_type = 'pending' 
                AND shinging_type = 'self' 
                AND customer_id = ?";
        $receiveNum = $this->locator->db->getOne($sql, $this->customerId);

        //组团中
        $groupNum = 0;
        $groupInfo = $this->models->orderGroup->getOrderGroup('*', array('setWhere' => 'group_status = 1'));
        if ($groupInfo) {
            foreach ($groupInfo as $row) {
                if ($row['group_type']['status'] == 'pending') {
                    $groupNum++;
                }
            }
        }

        //待评论
        $sql = "SELECT COUNT(*) FROM t_orders 
                WHERE order_status = 1 
                AND order_type = 'received' 
                AND customer_id = ?";
        $reviewNum = $this->locator->db->getOne($sql, $this->customerId);

        // print_r($receiveNum);die;
        return array(
            'levelColor' => $this->levelColor,
            'receiveNum' => $receiveNum,
            'groupNum' => $groupNum,
            'reviewNum' => $reviewNum,
        );
    }

    public function infoAction()
    {
        $this->layout(Layout::LAYOUT_UE);
        $this->layout->title = '我的资料';

        return array();
    }

    public function checkInAction()
    {
        $this->layout(Layout::LAYOUT_UE);
        $this->layout->title = '签到';

        return array();
    }

    public function qrcodeAction()
    {
        $this->layout(Layout::LAYOUT_UE);
        $this->layout->title = '我的邀请码';
        
        $openId = $_SESSION['openid'];
        $key = HTTP_SERVER . BASE_PATH . "default/index?key=";

        if ($this->funcs->isAjax()) {
            for($i=0;;$i++) {
                $code = strtoupper(substr($this->funcs->rand(), 0, 8));
                $where = sprintf("customer_invite_code = '%s'", $code);
                if (!$this->models->customer->getCustomerInfo($where)) {
                    break;
                }
            }

            $sql = "UPDATE t_customers SET customer_invite_code = ? WHERE customer_openid = ?";
            $status = $this->locator->db->exec($sql, $code, $openId);
            if ($status) {
                $key .= $code;
                $src = (string) $this->helpers->url('common/qrcode', array('key' => $this->funcs->encrypt($key, 'E', QRCODE_KEY)));
                return JsonModel::init('ok', '', array('src' => $src));
            } else {
                return new JsonModel('error', '刷新失败');
            }
        }

        $where = sprintf("customer_openid = '%s'", $openId);
    	$info = $this->models->customer->getCustomerInfo($where);

        $key .= $info['customer_invite_code'];

    	return array(
    		'key' => $key,
    	);
    }

    public function inviteAction()
    {
        $inviteCode = $this->param('key');
        $where = sprintf("customer_invite_code = '%s'", $inviteCode);
        $info = $this->models->customer->getCustomerInfo($where);

        print_r($info);die;
    }

    public function addressAction()
    {
        $this->layout->title = '我的地址';

        //用户信息
        $info = $this->models->customer->getCustomerInfo(sprintf("customer_id = %d", $this->customerId));

        //收货地址
        $sql = "SELECT c.*, d.district_name FROM t_customer_address c
                LEFT JOIN t_district d ON d.district_id = c.district_id
                WHERE customer_id = ?
                ORDER BY address_id DESC";
        $addressList = $this->locator->db->getAll($sql, $this->customerId);
        if ($addressList) {
            //把默认地址放前面
            $defaultKey = array_search($info['customer_default_address_id'], array_column($addressList, 'address_id'));
            if ($defaultKey !== false) {
                $default = $addressList[$defaultKey];
                unset($addressList[$defaultKey]);
                array_unshift($addressList, $default);
            }
        }

        // print_r($addressList);die;
        return array(
            'addressList' => $addressList,
            'info' => $info,
        );
    }

    public function addressEditAction()
    {
        $id = trim($this->param('id'));

        $this->layout->title = $id ? '编辑地址' : '新建地址';

        return array();
    }

    public function addressDefaultAction()
    {
        if (!$this->funcs->isAjax()) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        $id = trim($this->param('id'));
        $sql = "SELECT * FROM t_customer_address WHERE address_id = ?";
        $info = $this->locator->db->getRow($sql, $id);
        if (!$info) {
            return new JsonModel('error', '地址不存在');
        }

        //修改默认地址
        $sql = "UPDATE t_customers 
                SET customer_default_address_id = ? 
                WHERE customer_id = ?";
        $status = $this->locator->db->exec($sql, $id, $this->customerId);
        if ($status) {
            $redirect = $this->param('redirect');
            if ($redirect) {
                $redirect = str_replace("&amp;", "&", $redirect);
                $redirect = $this->funcs->getUrl($redirect);
                $redirect['params']['address'] = true;
                $redirect = $this->funcs->urlInit($redirect);
                return JsonModel::init('ok', '')->setRedirect($redirect);
            } else {
                return JsonModel::init('ok', '');
            }
        } else {
            return new JsonModel('error', '修改失败');
        }
    }

    public function addressDelAction()
    {
        if (!$this->funcs->isAjax()) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        $id = trim($this->param('id'));
        $sql = "DELETE FROM t_customer_address WHERE address_id = ?";
        $status = $this->locator->db->exec($sql, $id);
        if (!$status) {
            return new JsonModel('error', '删除失败');
        }

        $sql = "UPDATE t_customers 
                SET customer_default_address_id = 0 
                WHERE customer_id = ? 
                AND customer_default_address_id = ?";
        $this->locator->db->exec($sql, $this->customerId, $id);

        return JsonModel::init('ok', '');
    }

    public function groupDetailAction()
    {
        $id = trim($this->param('id'));
        $info = $this->models->orderGroup->getOrderGroupById($id);
        if (!$info) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        $this->layout(Layout::LAYOUT_UE);
        $this->layout->title = '订单详情';
        
        $sql = "SELECT * FROM t_orders 
                WHERE product_id = ? 
                AND customer_id = ?";
        $order = $this->locator->db->getRow($sql, $info['product_id'], $this->customerId);
        $order['product'] = $this->models->product->getProductById($info['product_id']);

        //剩余时间
        $info['time'] = (strtotime($info['group_time']) + $order['product']['product_group_time'] * 86400) - time();

        // print_r($order);die;
        return array(
            'order' => $order,
            'info' => $info,
        );
    }
}
