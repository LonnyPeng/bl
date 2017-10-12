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
        $this->layout->title = '个人中心';

        //待领取
        $sql = "SELECT COUNT(*) FROM t_orders 
                WHERE order_status = 1 
                AND order_type IN ('pending', 'shipped')
                AND customer_id = ?";
        $receiveNum = $this->locator->db->getOne($sql, $this->customerId);

        //组团中
        $sql = "SELECT COUNT(*) FROM t_orders 
                WHERE order_status = 1 
                AND order_type IN ('group')
                AND customer_id = ?";
        $groupNum = $this->locator->db->getOne($sql, $this->customerId);

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
        $this->layout->title = '我的资料';

        if ($this->funcs->isAjax()) {
            
        }

        // print_r($this->locator->get('Profile')['customer_headimg']);die;
        return array();
    }

    public function checkInAction()
    {
        $this->layout->title = '签到';
        $this->layout->class = 'calendar';

        return array();
    }

    public function scoreAction()
    {
        $this->layout->title = '我的积分';

        return array();
    }

    public function qrcodeAction()
    {
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

        $sql = "SELECT c.*, d.district_name FROM t_customer_address c
                LEFT JOIN t_district d ON d.district_id = c.district_id
                WHERE address_id = ?";
        $info = $this->locator->db->getRow($sql, $id);

        if ($this->funcs->isAjax()) {
            if (!$_POST['user_name']) {
                return new JsonModel('error', '请输入姓名');
            }
            if (!$_POST['user_tel']) {
                return new JsonModel('error', '请输入手机号码');
            } elseif (!isPhone($_POST['user_tel'])) {
                return new JsonModel('error', '手机格式错误');
            }
            if (!$_POST['user_address_city']) {
                return new JsonModel('error', '请选择省市区');
            }
            if (!$_POST['user_address_detail']) {
                return new JsonModel('error', '请填写详细地址');
            }

            $addressCity = preg_replace("/[ ]{2,}/", " ", trim($_POST['user_address_city']));
            $addressCity = explode(" ", $addressCity);
            if (count($addressCity) > 1) {
                $city = $addressCity[1];
            } else {
                $city = $addressCity[0];
            }
            $districtInfo = $this->models->district->getDistrictInfo(array(sprintf("district_name LIKE'%s%%'", $city)));

            $map = array(
                'district_id' => $districtInfo['district_id'],
                'user_address' => implode(" ", $addressCity) . '  ' . preg_replace("/[ ]{2,}/", " ", trim($_POST['user_address_detail'])),
                'user_name' => trim($_POST['user_name']),
                'user_tel' => trim($_POST['user_tel']),
            );
            $set = "district_id = :district_id, user_address= :user_address, user_name = :user_name, user_tel = :user_tel";
            if ($info) {
                $map['address_id'] = $id;
                $sql = "UPDATE t_customer_address SET {$set} WHERE address_id = :address_id";
            } else {
                $map['customer_id'] = $this->customerId;
                $set .= ',customer_id = :customer_id';
                $sql = "INSERT INTO t_customer_address SET {$set}";
            }

            $status = $this->locator->db->exec($sql, $map);
            if ($status) {
                return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('customer/address', array('redirect' => $this->param('redirect'))));
            } else {
                return new JsonModel('error', '提交失败');
            }
        }

        if ($info) {
            $address = explode("  ", $info['user_address']);
            if (count($address) > 1) {
                $info['user_address_city'] = $address[0];
                $info['user_address_detail'] = $address[1];
            } else {
                $info['user_address_city'] = '';
                $info['user_address_detail'] = $address[0];
            }
        }

        // print_r($info);die;
        return array(
            'info' => $info,
        );
    }

    public function collectionAction()
    {
        $this->layout->title = '编辑地址';

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
