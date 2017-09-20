<?php

namespace App\Model;

class CustomerModel extends CommonModel
{
    protected $name = 't_customers c';

    public function getCustomerByOpenid($openid)
    {
        $sql = "SELECT * FROM $this->name WHERE customer_openid = ?";
        return $this->locator->db->getRow($sql, $openid);
    }
}
