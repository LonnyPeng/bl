<?php

namespace App\Model;

class ShopModel extends CommonModel
{
    protected $name = 't_shops s';

    /**
     * Get count
     *
     */
    public function getCount($sqlInfo)
    {
        $sql = "SELECT COUNT(*) FROM $this->name";
        $sql = $this->setSql($sql, $sqlInfo);

        return $this->locator->db->getOne($sql);
    }

    /**
     * Get 
     *
     */
    public function getShop($files = '*', $sqlInfo = array())
    {
        $files = $this->setFile($files);
        $sql = "SELECT $files FROM $this->name";
        $sql = $this->setSql($sql, $sqlInfo);

        return $this->locator->db->getAll($sql);
    }

    public function getShopByMemberId($memberId)
    {
        $sql = "SELECT * FROM t_shops 
        		WHERE member_id = ? 
        		AND shop_status = 1 
        		AND shop_review = 1";

        return $this->locator->db->getRow($sql, $memberId);
    }

    public function getShopById($shopId)
    {
        $sql = "SELECT * FROM $this->name WHERE shop_id = ?";
        return $this->locator->db->getRow($sql, $shopId);
    }

    public function getShopByName($name)
    {
        $sql = "SELECT * FROM t_shops 
                WHERE shop_name = ? 
                AND shop_status = 1 
                AND shop_review = 1";

        return $this->locator->db->getRow($sql, $name);
    }

    public function getShopTypePair()
    {
        $sql = "SELECT type_id, type_name 
                FROM t_shop_types
                WHERE type_status = 1
                ORDER BY type_sort DESC, type_id ASC";
        return (array) $this->locator->db->getPairs($sql);
    }
}