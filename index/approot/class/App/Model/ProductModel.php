<?php

namespace App\Model;

class ProductModel extends CommonModel
{
	protected $name = 't_products p';

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
	 * Get list
	 *
	 */
	public function getProduct($files = '*', $sqlInfo = array())
	{
	    $files = $this->setFile($files);
	    $sql = "SELECT $files FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);

	    return $this->locator->db->getAll($sql);
	}

	public function getProductById($id)
	{
	    $sql = "SELECT * FROM $this->name
	    		LEFT JOIN t_district d ON p.district_id = d.district_id
	    		LEFT JOIN t_product_attr pa ON p.attr_id = pa.attr_id
	    		WHERE product_id = ?";
	    $productInfo = (array) $this->locator->db->getRow($sql, $id);
	    if (isset($productInfo['product_id'])) {
	    	$data = array();
	    	$sql = "SELECT * FROM t_product_images 
	    			WHERE product_id = ? 
	    			AND image_status = 1
	    			ORDER BY image_type ASC, image_sort DESC, image_id DESC";
	    	$productImages = $this->locator->db->getAll($sql, $id);
	    	if ($productImages) {
	    		foreach ($productImages as $key => $row) {
	    			if (isset($data[$row['image_type']])) {
	    				$data[$row['image_type']][] = $row;
	    			} else {
	    				$data[$row['image_type']] = array($row);
	    			}
	    		}
	    	}

	    	$productInfo['images'] = $data;

	    	$sql = "SELECT * FROM t_product_quantity pq
	    			LEFT JOIN t_shops s ON pq.shop_id = s.shop_id
	    			WHERE pq.product_id = ?";
	    	$productInfo['shop_quantity'] = $this->locator->db->getAll($sql, $id);

	    	//收藏人数
	    	$sql = "SELECT COUNT(*) FROM t_customer_collections WHERE product_id = ?";
	    	$productInfo['collection_num'] = $this->locator->db->getOne($sql, $id);

	    	//是否收藏
	    	$sql = "SELECT collection_id 
	    			FROM t_customer_collections 
	    			WHERE product_id = ? 
	    			AND customer_id = ?";
	    	$productInfo['collection_id'] = $this->locator->db->getOne($sql, $id, $this->locator->get('Profile')['customer_id']);
	    }

	    return $productInfo;
	}

	public function getAttrPair()
	{
		$sql = "SELECT attr_id, attr_name 
				FROM t_product_attr
				WHERE attr_status = 1
				ORDER BY attr_sort DESC, attr_id DESC";

		return $this->locator->db->getPairs($sql);
	}

	public function getProductId($where = array())
	{
		$sql = "SELECT product_id FROM t_products";
		$sql = $this->setWhere($sql, $where);

		return $this->locator->db->getOne($sql);
	}
}