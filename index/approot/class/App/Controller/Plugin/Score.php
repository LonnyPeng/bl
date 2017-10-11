<?php

namespace App\Controller\Plugin;

use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;

class Score implements ServiceLocatorAwareInterface
{
    const JFDHCJ = '积分兑换抽奖';
    const CJHDJF = '抽奖获得积分';
    const GMSP = '购买商品';
	const PLSP = '评论商品';

	protected $locator = null;

	/**
	 * 
	 * @param 'type' => 'buy', 'des' => Score::JFDHCJ, 'score' => 10
	 */
    public function __invoke($row = array())
    {
    	$customeId = $this->locator->get('Profile')['customer_id'];

    	//修改积分
    	$sql = "UPDATE t_customers 
    			SET customer_score = customer_score + :customer_score 
    			WHERE customer_id = :customer_id";
    	$status = $this->locator->db->exec($sql, array(
    		'customer_score' => $row['type'] == 'have' ? $row['score'] : 0 - $row['score'],
    		'customer_id' => $customeId,
    	));
    	if (!$status) {
    		return false;
    	}

    	//记录积分日志
    	$sql = "INSERT INTO t_customer_score_log 
    			SET customer_id = :customer_id,
    			score_type = :score_type,
    			score_des = :score_des,
    			score_quantity = :score_quantity";
    	$this->locator->db->exec($sql, array(
    		'customer_id' => $customeId,
    		'score_type' => $row['type'],
    		'score_type' => $row['type'],
    		'score_des' => $row['des'],
    		'score_quantity' =>$row['score'],
    	));

    	return true;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocator $serviceLocator
     * @return Controller
     */
    public function setServiceLocator(ServiceLocator $serviceLocator)
    {
        $this->locator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocator
     */
    public function getServiceLocator()
    {
        return $this->locator;
    }
}