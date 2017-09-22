<?php

namespace App\Model;

class CustomerModel extends CommonModel
{
    protected $name = 't_customers c';

    public function getCount($sqlInfo)
    {
        $sql = "SELECT COUNT(*) FROM $this->name";
        $sql = $this->setSql($sql, $sqlInfo);

        return $this->locator->db->getOne($sql);
    }

    public function getCustomer($files = '*', $sqlInfo = array())
    {
        $files = $this->setFile($files);
        $sql = "SELECT $files FROM $this->name";
        $sql = $this->setSql($sql, $sqlInfo);

        return $this->locator->db->getAll($sql);
    }

    public function getCustomerById($id)
    {
        $sql = "SELECT c.*, d.district_name 
                FROM $this->name 
                LEFT JOIN t_district d ON d.district_id = c.district_id
                WHERE customer_id = ?";
        return $this->locator->db->getRow($sql, $id);
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
        return $this->locator->db->getOne($sql, $id);
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
