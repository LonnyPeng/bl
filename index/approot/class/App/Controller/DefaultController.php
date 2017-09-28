<?php

namespace App\Controller;

use Framework\Utils\Http;

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

        // print_r($imageList);die;
        return array(
            'attrList' => $attrList,
            'imageList' => $imageList,
            'recommendList' => $recommendList,
        );
    }

    public function cityAction()
    {
        $this->layout->title = '选择城市';

        //获取最近访问的城市
        $sql = "SELECT c.district_id, d.district_name, d.district_status FROM t_customer_login_log c 
                LEFT JOIN t_district d ON d.district_id = c.district_id
                WHERE c.customer_id = ?
                GROUP BY c.district_id
                ORDER BY log_id DESC
                LIMIT 0, 10";
        $logList = $this->locator->db->getAll($sql, $this->locator->get('Profile')['customer_id']);

        //获取城市
        $sqlInfo = array(
            'setWhere' => 'district_status = 1',
            'setOrderBy' => 'CONVERT(d.district_name USING GBK) ASC',
        );

        $districtList = $this->models->district->getDistrict('*', $sqlInfo);
        if ($districtList) {
            foreach ($districtList as $key => $row) {
                if (isset($districtList[$row['district_initial']])) {
                    $districtList[$row['district_initial']][] = $row;
                } else {
                    $districtList[$row['district_initial']] = array($row);
                }

                unset($districtList[$key]);
            }
        }

        // print_r($districtList);die;
        return array(
            'logList' => $logList,
            'districtList' => $districtList,
        );
    }

    public function changeCityAction()
    {
        $id = trim($this->param('id'));
        $info = $this->models->district->getDistrictInfo(sprintf("district_id = %d", $id));
        if ($info) {
            //切换城市
            $_SESSION['customer_info']['city'] = $info['district_name'];
            $_SESSION['customer_info']['district_id'] = $info['district_id'];

            //保存记录
            $sql = "INSERT INTO t_customer_login_log 
                    SET customer_id = :customer_id, 
                    district_id = :district_id, 
                    log_ip = :log_ip";
            $this->locator->db->exec($sql, array(
                'customer_id' => $this->locator->get('Profile')['customer_id'],
                'district_id' => $_SESSION['customer_info']['district_id'],
                'log_ip' => Http::getIp(),
            ));
        }

        if ($info && $info['district_status']) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        } else {
            $this->funcs->redirect($this->helpers->url('default/no-product'));
        }
    }

    public function noProductAction()
    {
        $this->layout->title = '城市无商品页面';

        return array();
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
