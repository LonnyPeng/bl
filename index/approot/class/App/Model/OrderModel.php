<?php

namespace App\Model;

class OrderModel extends CommonModel
{
    protected $name = 't_orders o';

    /**
     * 
     *
     */
    public function getCount($sqlInfo)
    {
        $sql = "SELECT COUNT(*) FROM $this->name";
        $sql = $this->setSql($sql, $sqlInfo);

        return $this->locator->db->getOne($sql);
    }

    /**
     * 
     *
     */
    public function getOrder($files = '*', $sqlInfo = array())
    {
        $files = $this->setFile($files);
        $sql = "SELECT $files FROM $this->name";
        $sql = $this->setSql($sql, $sqlInfo);

        return $this->locator->db->getAll($sql);
    }

    public function getOrderInfo($where = array())
    {
        $sql = "SELECT * FROM $this->name";
        $sql = $this->setWhere($sql, $where);

        return $this->locator->db->getRow($sql);
    }
}
