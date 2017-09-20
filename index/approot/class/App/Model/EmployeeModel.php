<?php

namespace App\Model;

class EmployeeModel extends CommonModel
{
    protected $name = 't_employees e';

    public function getEmployeePair()
    {
        $sql = "SELECT employee_name 
                FROM $this->name
                WHERE shop_id = ?";
        return $this->locator->db->getColumn($sql, $_SESSION['shop_id']);
    }

    public function getEmpIdByName($emplyeeName)
    {
    	$sql = "SELECT employee_id 
    	        FROM $this->name
    	        WHERE employee_name = ? 
    	        AND shop_id = ?";
    	return $this->locator->db->getOne($sql, $emplyeeName, $_SESSION['shop_id']);
    }
}
