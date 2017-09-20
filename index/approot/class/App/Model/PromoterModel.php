<?php

namespace App\Model;

class PromoterModel extends CommonModel
{
    protected $name = 't_promoters p';

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
    public function getPromoter($files = '*', $sqlInfo = array())
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
    public function getPromoterById($promoterId)
    {
        $sql = "SELECT * FROM $this->name WHERE promoter_id = ?";
        return $this->locator->db->getRow($sql, $promoterId);
    }

    public function getPromoterByCode($promoterCode)
    {
        $sql = "SELECT * FROM $this->name 
                WHERE promoter_code = ? 
                AND shop_id = ?";
        return $this->locator->db->getRow($sql, $promoterCode, $_SESSION['shop_id']);
    }

    public function getPromoterPair()
    {
        $sql = "SELECT promoter_id, promoter_name 
                FROM $this->name
                WHERE shop_id = ?";
        return $this->locator->db->getPairs($sql, $_SESSION['shop_id']);
    }

    public function getPromoterByName($promoterName)
    {
        $sql = "SELECT * FROM $this->name WHERE promoter_name = ? AND shop_id = ?";
        return $this->locator->db->getRow($sql, $promoterName, $_SESSION['shop_id']);
    }
}
