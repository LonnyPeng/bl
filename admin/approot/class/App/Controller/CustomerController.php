<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;

class CustomerController extends AbstractActionController
{
    public function init()
    {
        parent::init();
    }

    public function listAction()
    {
    	$where = array();
    	$limit = '';
    	if ($this->param('customer_name')) {
    		$where[] = sprintf("customer_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('customer_name'))) . '%'));
    	}

    	$count = $this->models->customer->getCount(array('setWhere' => $where));
    	$this->helpers->paginator($count, 10);
    	$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

    	$files = 'c.*, d.district_name';
    	$sqlInfo = array(
    		'setJoin' => 'LEFT JOIN t_district d ON d.district_id = c.district_id',
    		'setWhere' => $where,
    		'setLimit' => $limit,
    		'setOrderBy' => 'customer_id DESC',
    	);

    	$customerList = $this->models->customer->getCustomer($files, $sqlInfo);
        if ($customerList) {
            foreach ($customerList as $key => $row) {
                $customerList[$key]['level_name'] = $this->models->customer->getCustomerLevel($row['customer_id']);
            }
        }

    	// print_r($customerList);die;
    	return array(
    		'customerList' => $customerList,
    	);
    }

    public function detailAction()
    {
    	$id = $this->param('id');
    	$info = $this->models->customer->getCustomerById($id);
    	if (!$info) {
    		$this->funcs->redirect($this->helpers->url('customer/list'));
    	} else {
            $info['level_name'] = $this->models->customer->getCustomerLevel($id);
            $info['history_score'] = $this->models->customer->getHistoryScore($id);
        }

    	//最近登录城市
    	$sql = "SELECT c.log_time, d.district_name FROM t_customer_login_log c 
    			LEFT JOIN t_district d ON d.district_id = c.district_id
    			WHERE c.customer_id = ?
    			ORDER BY log_id DESC
    			LIMIT 0, 10";
    	$cityList = $this->locator->db->getAll($sql, $id);

    	//历史搜索
    	$sql = "SELECT log_name, log_time FROM t_customer_search_log
    			WHERE customer_id = ?
    			ORDER BY log_id DESC
    			LIMIT 0, 10";
    	$searchList = $this->locator->db->getAll($sql, $id);

    	//收货地址
    	$sql = "SELECT c.*, d.district_name FROM t_customer_address c
    			LEFT JOIN t_district d ON d.district_id = c.district_id
    			WHERE customer_id = ?
    			ORDER BY address_id DESC";
    	$addressList = $this->locator->db->getAll($sql, $id);
    	if ($addressList) {
    		//把默认地址放前面
    		$defaultKey = array_search($info['customer_default_address_id'], array_column($addressList, 'address_id'));
    		if ($defaultKey !== false) {
    			$default = $addressList[$defaultKey];
    			unset($addressList[$defaultKey]);
    			array_unshift($addressList, $default);
    		}
    	}

    	// print_r($addressList);die;
    	return array(
    		'info' => $info,
    		'cityList' => $cityList,
    		'searchList' => $searchList,
    		'addressList' => $addressList,
    	);
    }

    public function editAction()
    {
        if (!$this->funcs->isAjax()) {
            $this->funcs->redirect($this->helpers->url('customer/list'));
        }

        $id = $this->param('id');
        $type = $this->param('type');
        if ($type == 'on') {
            $status = 1;
        } else {
            $status = 0;
        }

        $sql = "UPDATE t_customers 
        		SET customer_status = ? 
        		WHERE customer_id = ?";
        $status = $this->locator->db->exec($sql, $status, $id);
        if ($status) {
            return JsonModel::init('ok', '成功')->setRedirect('reload');
        } else {
            return new JsonModel('error', '失败');
        }
    }
}
