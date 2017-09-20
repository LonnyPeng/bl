<?php

namespace App\Model;

class ShopuserModel extends CommonModel
{
    protected $name = 't_shopusers s';

    /**
     * Get member count
     *
     */
    public function getCount($sqlInfo)
    {
        $sql = "SELECT COUNT(*) FROM $this->name";
        $sql = $this->setSql($sql, $sqlInfo);

        return $this->locator->db->getOne($sql);
    }

    /**
     * Get member
     *
     */
    public function getShopuser($files = '*', $sqlInfo = array())
    {
        $files = $this->setFile($files);
        $sql = "SELECT $files FROM $this->name";
        $sql = $this->setSql($sql, $sqlInfo);

        return $this->locator->db->getAll($sql);
    }

    /**
     * Get member by member_id
     *
     * @param string|integer $memberId
     * @return array
     */
    public function getShopuserById($shopuserId)
    {
        $sql = "SELECT * FROM $this->name WHERE shopuser_id = ?";
        return $this->locator->db->getRow($sql, $shopuserId);
    }

    public function getShopuserByCode($shopuserCode, $shopId = '')
    {
        if (!$shopId) {
            $shopId = $_SESSION['shop_id'];
        }
        
        $sql = "SELECT * FROM $this->name 
                WHERE shopuser_code = ? 
                AND shop_id = ?";
        return $this->locator->db->getRow($sql, $shopuserCode, $shopId);
    }

    public function getShopuserPair()
    {
        $sql = "SELECT shopuser_id, shopuser_name 
                FROM $this->name
                WHERE shop_id = ?";
        return $this->locator->db->getPairs($sql, $_SESSION['shop_id']);
    }
}
