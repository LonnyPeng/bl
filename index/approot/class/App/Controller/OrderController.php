<?php

namespace App\Controller;


class OrderController extends AbstractActionController
{
    public function init()
    {
        parent::init();
    }

    public function qrcodeAction()
    {
        $orderNum = "0123456";
        
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
