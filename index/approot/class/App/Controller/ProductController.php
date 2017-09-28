<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;

class ProductController extends AbstractActionController
{
	public function init() {
	    parent::init();
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

		return array(
			'attrList' => $this->models->product->getAttrPair(),
		);
	}

	public function detailAction()
	{
		return array();
	}
}
