<?php

namespace App\Controller;

use App\Controller\Plugin\Layout;

class CartController extends AbstractActionController
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
        $id = trim($this->param('id'));
        $info = $this->models->product->getProductById($id);
        if (!$info) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        $this->layout->title = '确认订单';

        $addressInfo = array();
        //用户默认地址
        $customerInfo = $this->models->customer->getCustomerInfo(sprintf("customer_id = %d", $this->customerId));
        if ($customerInfo['customer_default_address_id']) {
            $sql = "SELECT * FROM t_customer_address WHERE address_id = ?";
            $addressInfo = $this->locator->db->getRow($sql, $customerInfo['customer_default_address_id']);
        }
        
        // print_r($addressInfo);die;
        return array(
            'info' => $info,
            'addressInfo' => $addressInfo,
        );
    }
}
