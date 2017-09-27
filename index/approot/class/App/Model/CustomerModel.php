<?php

namespace App\Model;

class CustomerModel extends CommonModel
{
    protected $name = 't_customers c';

    public function getCustomerInfo($where = array())
    {
        $sql = "SELECT c.*, d.district_name 
                FROM $this->name 
                LEFT JOIN t_district d ON d.district_id = c.district_id";
        $sql = $this->setWhere($sql, $where);
        $result = $this->locator->db->getRow($sql);
        if ($result) {
            $result['level_name'] = $this->getCustomerLevel($result['customer_id']);
            $result['level_id'] = $this->getCustomerLevelId($result['level_name']);;
        }

        return $result;
    }

    public function getCustomerLevel($id)
    {
        $sql = "SELECT level_name
                FROM t_customer_score_level 
                WHERE level_status = 1 
                AND level_score <= (
                    SELECT SUM(score_quantity) 
                    FROM t_customer_score_log 
                    WHERE customer_id = ? 
                    AND score_type = 'have'
                )
                ORDER BY level_score DESC
                LIMIT 0, 1";
        $levelName = $this->locator->db->getOne($sql, $id);
        if (!$levelName) {
            $sql = "SELECT level_name 
                    FROM t_customer_score_level 
                    WHERE level_status = 1 
                    ORDER BY level_score ASC 
                    LIMIT 0, 1";
            $levelName = $this->locator->db->getOne($sql);
        }
        
        return $levelName;
    }

    public function getCustomerLevelId($name)
    {
        $sql = "SELECT level_id 
                FROM t_customer_score_level 
                WHERE level_status = 1 
                AND level_name = ?";
        return $this->locator->db->getOne($sql, $name);
    }

    public function getHistoryScore($id)
    {
        $sql = "SELECT SUM(score_quantity) 
                    FROM t_customer_score_log 
                    WHERE customer_id = ? 
                    AND score_type = 'have'";
        return $this->locator->db->getOne($sql, $id);
    }

    public function getScoreLevel()
    {
        $sql = "SELECT level_id, level_name FROM t_customer_score_level 
                WHERE level_status = 1 
                ORDER BY level_score ASC";
        return $this->locator->db->getPairs($sql);
    }
}
