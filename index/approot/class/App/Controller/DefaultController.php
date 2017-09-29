<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;
use Framework\Utils\Http;

class DefaultController extends AbstractActionController
{
    public $districtId = null;
    public $customerId = null;

    public function init()
    {
        parent::init();

        $this->districtId = $_SESSION['customer_info']['district_id'];
        $this->customerId = $this->locator->get('Profile')['customer_id'];
    }

    public function indexAction()
    {
        $this->layout->title = '白领首页';

        //获取商品类别
        $attrList = $this->models->product->getAttrPair();

        //获取焦点图
        $sql = "SELECT * FROM t_images 
                WHERE district_id = ? 
                ORDER BY image_sort DESC, image_id DESC";
        $imageList = $this->locator->db->getAll($sql, $this->districtId);

        //获取首页推荐商品
        $sql = "SELECT * FROM t_recommends 
                WHERE district_id = ? 
                ORDER BY recommend_sort DESC, recommend_id DESC
                LIMIT 0, 2";
        $recommendList = $this->locator->db->getAll($sql, $this->districtId);
        if ($recommendList) {
            foreach ($recommendList as $key => $row) {
                if ($row['product_code']) {
                    $recommendList[$key]['product_id'] = $this->models->product->getProductId(sprintf("product_code = '%s'", $row['product_code']));
                } else {
                    $recommendList[$key]['product_id'] = '';
                }
            }
        }

        // print_r($recommendList);die;
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
        $sql = "SELECT DISTINCT(c.district_id), d.district_name, d.district_status FROM t_customer_login_log c 
                LEFT JOIN t_district d ON d.district_id = c.district_id
                WHERE c.customer_id = ?
                ORDER BY log_id DESC
                LIMIT 0, 5";
        $logList = $this->locator->db->getAll($sql, $this->customerId);
        
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
                'customer_id' => $this->customerId,
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

    public function searchAction()
    {
        $this->layout->title = '搜索';

        $searchList = $productMain = $productMinor = $productList = array();

        $where = array(
            'p.product_status = 1',
            sprintf("p.district_id = %d", $this->districtId),
        );

        $productName = trim($this->param('product_name'));
        if ($productName) {
            //记录搜索记录
            $map = array(
                'customer_id' => $this->customerId,
                'log_name' => $productName,
            );
            $sql = "DELETE FROM t_customer_search_log WHERE customer_id = :customer_id AND log_name = :log_name";
            $this->locator->db->exec($sql, $map);
            $sql = "INSERT INTO t_customer_search_log SET customer_id = :customer_id, log_name = :log_name";
            $this->locator->db->exec($sql, $map);

            if ($this->param('product_name')) {
                $where[] = sprintf("p.product_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('product_name'))) . '%'));
            }

            $sqlInfo = array(
                'setWhere' => $where,
                'setLimit' => '0, 20',
                'setOrderBy' => 'attr_id ASC, product_sort DESC, product_id DESC',
            );

            $productList = $this->models->product->getProduct('*', $sqlInfo);
            if ($productList) {
                foreach ($productList as $key => $row) {
                    $productList[$key] = $this->models->product->getProductById($row['product_id']);
                }
            }
        } else {
            //历史搜索
            $sql = "SELECT log_name, log_time FROM t_customer_search_log
                    WHERE customer_id = ?
                    ORDER BY log_id DESC
                    LIMIT 0, 10";
            $searchList = $this->locator->db->getAll($sql, $this->customerId);

            //获取主要推荐
            $sql = "SELECT h.product_id FROM t_hot_products h
                    LEFT JOIN t_products p ON p.product_id = h.product_id
                    WHERE h.hot_type = 'main'
                    AND h.district_id = ? AND " . implode(" AND ", $where) . "
                    ORDER BY h.hot_id DESC
                    LIMIT 0, 1";
            $productId = $this->locator->db->getOne($sql, $this->districtId);
            if ($productId) {
                $productMain = $this->models->product->getProductById($productId);
            }

            //获取次要要推荐
            $sql = "SELECT p.* FROM t_hot_products h
                    LEFT JOIN t_products p ON p.product_id = h.product_id
                    WHERE h.hot_type = 'minor'
                    AND h.district_id = ? AND " . implode(" AND ", $where) . "
                    ORDER BY h.hot_id DESC
                    LIMIT 0, 10";
            $productMinor = $this->locator->db->getAll($sql, $this->districtId);
        }

        // print_r($productList);die;
        return array(
            'productList' => $productList,
            'searchList' => $searchList,
            'productMain' => $productMain,
            'productMinor' => $productMinor,
        );
    }

    public function searchClearAction()
    {
        $sql = "SELECT log_id FROM t_customer_search_log
                WHERE customer_id = ?
                ORDER BY log_id DESC
                LIMIT 0,10";
        $logIds = $this->locator->db->getColumn($sql, $this->customerId);
        if (!$logIds) {
            $this->funcs->redirect($this->helpers->url('default/search'));
        }

        $sql = "DELETE FROM t_customer_search_log WHERE log_id IN (%s)";
        $sql = sprintf($sql, implode(",", $logIds));
        $this->locator->db->exec($sql);

        $this->funcs->redirect($this->helpers->url('default/search'));
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
}
