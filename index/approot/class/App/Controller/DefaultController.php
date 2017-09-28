<?php

namespace App\Controller;

class DefaultController extends AbstractActionController
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $districtId = $_SESSION['customer_info']['district_id'];

        $this->layout->title = '白领首页';

        //获取商品类别
        $sql = "SELECT * FROM t_product_attr 
                ORDER BY attr_status DESC, attr_sort DESC, attr_id DESC";
        $attrList = $this->locator->db->getAll($sql);

        //获取焦点图
        $sql = "SELECT * FROM t_images 
                WHERE district_id = ? 
                ORDER BY image_sort DESC, image_id DESC";
        $imageList = $this->locator->db->getAll($sql, $districtId);

        //获取首页推荐商品
        $sql = "SELECT * FROM t_recommends 
                WHERE district_id = ? 
                ORDER BY recommend_sort DESC, recommend_id DESC
                LIMIT 0, 2";
        $recommendList = $this->locator->db->getAll($sql, $districtId);

        return array(
            'attrList' => $attrList,
            'imageList' => $imageList,
            'recommendList' => $recommendList,
        );
    }

    public function redisAction()
    {
    	$locator = $this->locator;
    	$cache = $locator->get('Framework\Cache\Redis');
    	$topMenu = $cache->get('test', function() use($locator) {
    	    return 'lonny';
    	}, 100);

    	return false;
    }

    public function testAction()
    {
        return array();
    }
}
