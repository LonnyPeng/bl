<?php

namespace App\Controller;

use Framework\Utils\Http;
use Framework\View\Model\JsonModel;
use Framework\View\Model\ViewModel;

class BasicController extends AbstractActionController
{
	public function init()
	{
	    parent::init();
	}

	public function districtListAction()
	{
		$where = array();
		if ($this->param('district_name')) {
			$where[] = sprintf("d.district_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('district_name'))) . '%'));
		}
		if ($this->param('district_status') === '0' || $this->param('district_status') === '1') {
			$where[] = sprintf("d.district_status = %d", $this->param('district_status'));
		}

		$count = $this->models->district->getCount(array('setWhere' => $where));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = '*';
		$sqlInfo = array(
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'CONVERT(d.district_name USING GBK) ASC',
		);

		$districtList = $this->models->district->getDistrict($files, $sqlInfo);

		// print_r($districtList);die;
	    return array(
	    	'districtList' => $districtList,
	    );
	}

	public function districtEditAction()
	{
		$districtId = $this->param('id');
		$districtInfo = array();
		if ($districtId) {
			$districtInfo = $this->models->district->getDistrictById($districtId);
		}

		if ($this->funcs->isAjax()) {
			$districtName = trim($_POST['district_name']);
			if (!$districtName) {
				return new JsonModel('error', '请输入城市名');
			}

			$map = array(
				'district_name' => trim($_POST['district_name']),
				'district_status' => $_POST['district_status'],
			);
			$set = "district_name = :district_name,
					district_status = :district_status";

			if (!$districtId) {
				$sql = "INSERT INTO t_district SET $set";
			} else {
				$map['district_id'] = $districtId;
				$sql = "UPDATE t_district SET $set
						WHERE district_id = :district_id";
			}

			$status = $this->locator->db->exec($sql, $map);
			if ($status) {
				return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('basic/district-list'));
			} else {
				return new JsonModel('error', '失败');
			}
		}
		return array(
			'districtInfo' => $districtInfo,
		);
	}

	public function districtDelAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$districtId = $this->param('id');
		
		$sql = "DELETE FROM t_district WHERE district_id = ?";
		$status = $this->locator->db->exec($sql, $districtId);
		if ($status) {
			return JsonModel::init('ok', '删除成功');
		} else {
			return new JsonModel('error', '删除失败');
		}
	}

	public function districtSearchAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$name = trim($this->param('q'));

		$field = 'district_name';
		$where = sprintf("district_name LIKE '%s'", addslashes('%' . $this->helpers->escape($name) . '%'));
		$sql = "SELECT district_name 
				FROM t_district 
				WHERE $where 
				ORDER BY CONVERT(district_name USING GBK) ASC 
				LIMIT 0, 20";
		$result = $this->locator->db->getColumn($sql);
		return JsonModel::init('ok', '', $result);
	}
}