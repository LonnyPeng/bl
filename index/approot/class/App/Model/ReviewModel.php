<?php

namespace App\Model;

class ReviewModel extends CommonModel
{
	protected $name = 't_reviews r';

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
	public function getReview($files = '*', $sqlInfo = array())
	{
	    $files = $this->setFile($files);
	    $sql = "SELECT $files FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);

	    return $this->locator->db->getAll($sql);
	}

	public function getReviewById($id)
	{
	    $sql = "SELECT r.*, c.* FROM $this->name
	    		LEFT JOIN t_customers c ON c.customer_id = r.customer_id
	    		WHERE review_id = ?
	    		AND review_attr <> 'unread'";
	    $info = $this->locator->db->getRow($sql, $id);
	    if ($info) {
	    	$sql = "SELECT * FROM t_review_images 
	    			WHERE review_id = ? 
	    			AND image_status = 1
	    			ORDER BY image_id DESC";
	    	$reviewImages = $this->locator->db->getAll($sql, $id);
	    	$info['images'] = $reviewImages;
	    }

	    return $info;
	}
}