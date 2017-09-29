<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;

class ProductController extends AbstractActionController
{
	public $districtId = null;
	public $customerId = null;

	public function init() 
	{
	    parent::init();

	    $this->districtId = $_SESSION['customer_info']['district_id'];
	    $this->customerId = $this->locator->get('Profile')['customer_id'];
	}

	public function listAction()
	{
		$attrId = trim($this->param('attr'));
		$sql = "SELECT * FROM t_product_attr WHERE attr_id = ?";
		$attrInfo = $this->locator->db->getRow($sql, $attrId);
		if (!$attrInfo) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$this->layout->title = sprintf('%s列表', $attrInfo['attr_name']);

		$limit = array(0, 2);
		$where = array(
		    'p.product_status = 1',
		    sprintf("p.district_id = %d", $this->districtId),
		    sprintf("p.attr_id = %d", $attrInfo['attr_id']),
		);

		if ($this->funcs->isAjax() && $this->param('type') == 'page') {
		    $limit[0] = $this->param('pageSize') * $limit[1];
		}
		
		$sqlInfo = array(
		    'setWhere' => $where,
		    'setLimit' => $limit,
		    'setOrderBy' => 'product_sort DESC, product_id DESC',
		);

		$productList = $this->models->product->getProduct('*', $sqlInfo);
		if ($productList) {
		    foreach ($productList as $key => $row) {
		        $productList[$key] = $this->models->product->getProductById($row['product_id']);
		    }
		}

		// print_r($productList);die;
		if ($this->funcs->isAjax() && $this->param('type') == 'page') {
		    if ($productList) {
		        foreach ($productList as $key => $row) {
		            foreach ($row as $ke => $value) {
		                if (!$value) {
		                    $productList[$key][$ke] = '';
		                }
		            }

		            $productList[$key]['image_path'] = (string) $this->helpers->uploadUrl($row['images']['home'][0]['image_path'], 'product');
		            $productList[$key]['product_price'] = $this->funcs->showValue($row['product_price']);
		            $productList[$key]['product_virtual_price'] = $this->funcs->showValue($row['product_virtual_price']);
		            $productList[$key]['collection_num'] = $row['collection_num'] ?: 0;
		            $productList[$key]['collection_status'] = $row['collection_id'] ? 'on' : '';
		            $productList[$key]['href'] = (string) $this->helpers->url('product/detail', array('id' => $row['product_id']));
		            $productList[$key]['button'] = $row['product_type'] == 2 ? '组团领' : '立即白领';
		        }

		        return JsonModel::init('ok', '', $productList);
		    } else {
		        return new JsonModel('error', '');
		    }
		} else {
		    return array(
		        'productList' => $productList,
		        'attrList' => $this->models->product->getAttrPair(),
		    );
		}
	}

	public function detailAction()
	{
		$id = trim($this->param('id'));
		$info = $this->models->product->getProductById($id);
		return array();
	}

	public function collectionAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$type = $this->param('type');
		if ($type == 'del') {
			$sql = "DELETE FROM t_customer_collections WHERE customer_id = ? AND product_id = ?";
		} else {
			$sql = "INSERT INTO t_customer_collections SET customer_id = ?, product_id = ?";
		}

		$status = $this->locator->db->exec($sql, $this->customerId, $id);
		if ($status) {
			return JsonModel::init('ok', '');
		} else {
			return new JsonModel('error', '失败');
		}
	}
}
