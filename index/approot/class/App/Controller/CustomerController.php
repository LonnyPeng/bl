<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;
use App\Controller\Plugin\Layout;
use App\Controller\Plugin\Score;
use EasyWeChat\Foundation\Application;

class CustomerController extends AbstractActionController
{
    public $districtId = null;
    public $customerId = null;
    private $app = null;
    public $levelColor = array('1' => '#f2f2f2', '2' => '#f6e802', '3' => '#fc4e47', '4' => '#fed043');
    public $levelIcon = array('1' => '&#xe60f;', '2' => '&#xe60e;', '3' => '&#xe613;', '4' => '&#xe615;');
    protected $imgType = array('image/jpeg', 'image/x-png', 'image/pjpeg', 'image/png');
    protected $imgMaxSize = 1024 * 1024 * 4; //4M

    public function init()
    {
        parent::init();

        $this->districtId = $_SESSION['customer_info']['district_id'];
        $this->customerId = $this->locator->get('Profile')['customer_id'];
    }

    public function indexAction()
    {
        $this->layout->title = '个人中心';

        //待领取
        $sql = "SELECT COUNT(*) FROM t_orders 
                WHERE order_status = 1 
                AND order_type IN ('pending', 'shipped')
                AND customer_id = ?";
        $receiveNum = $this->locator->db->getOne($sql, $this->customerId);

        //组团中
        $sql = "SELECT COUNT(*) FROM t_orders 
                WHERE order_status = 1 
                AND order_type IN ('group')
                AND customer_id = ?";
        $groupNum = $this->locator->db->getOne($sql, $this->customerId);

        //待评论
        $sql = "SELECT COUNT(*) FROM t_orders 
                WHERE order_status = 1 
                AND order_type = 'received' 
                AND customer_id = ?";
        $reviewNum = $this->locator->db->getOne($sql, $this->customerId);

        // print_r($receiveNum);die;
        return array(
            'levelColor' => $this->levelColor,
            'receiveNum' => $receiveNum,
            'groupNum' => $groupNum,
            'reviewNum' => $reviewNum,
        );
    }

    public function infoAction()
    {
        $this->layout->title = '我的资料';

        if ($this->funcs->isAjax()) {
            if ($_POST['customer_gender']) { //选择性别
                $sql = "UPDATE t_customers SET customer_gender = ? WHERE customer_id = ?";
                $this->locator->db->exec($sql, trim($_POST['customer_gender']), $this->customerId);

                $sql = "SELECT COUNT(*) 
                        FROM t_customer_score_log 
                        WHERE customer_id = ? 
                        AND score_type = 'have' 
                        AND score_des = ?";
                if (!$this->locator->db->getOne($sql, $this->customerId, Score::XZXB)) {
                    //改变积分
                    $result = $this->score(array(
                        'type' => 'have', 
                        'des' => Score::XZXB, 
                        'score' => 10,
                    ));
                }
            }

            if ($_POST['customer_age']) { //选择年龄段
                $sql = "UPDATE t_customers SET customer_age = ? WHERE customer_id = ?";
                $status = $this->locator->db->exec($sql, trim($_POST['customer_age']), $this->customerId);

                $sql = "SELECT COUNT(*) 
                        FROM t_customer_score_log 
                        WHERE customer_id = ? 
                        AND score_type = 'have' 
                        AND score_des = ?";
                if (!$this->locator->db->getOne($sql, $this->customerId, Score::XZNLD)) {
                    //改变积分
                    $result = $this->score(array(
                        'type' => 'have', 
                        'des' => Score::XZNLD, 
                        'score' => 10,
                    ));
                }
            }

            if ($_FILES['file']) {
                $row = $_FILES['file'];
                if (!$row['error'] && in_array($row['type'], $this->imgType) && $row['size'] <= $this->imgMaxSize) {
                    $dir = USER_DIR;

                    $filename = $row['tmp_name'];
                    $upFileName = date('Ymd') . '/';
                    $this->funcs->makeFile($dir . $upFileName);

                    $ext = pathinfo($row['name'], PATHINFO_EXTENSION);
                    $fileName = md5($row['name'] . $this->funcs->rand());
                    for($i=0;; $i++) {
                        if (file_exists($dir . $upFileName . $fileName . '.' . $ext)) {
                            $fileName = md5($row['name'] . $this->funcs->rand());
                        } else {
                            break;
                        }
                    }

                    $uploadFile = $upFileName . $fileName . '.' . $ext;
                    $uploadFilePath = $dir . $uploadFile;
                    move_uploaded_file($row['tmp_name'], $uploadFilePath);

                    //处理图片
                    $result = $this->funcs->setImage(USER_DIR . $uploadFile, USER_DIR, 64, 64);
                    if ($result['status']) {
                        $uploadFile = $result['content'];

                        $sql = "UPDATE t_customers SET customer_headimg = ? WHERE customer_id = ?";
                        $status = $this->locator->db->exec($sql, $uploadFile, $this->customerId);

                        //删除原图片
                        if (file_exists(USER_DIR . $this->locator->get('Profile')['customer_headimg'])) {
                            unlink(USER_DIR . $this->locator->get('Profile')['customer_headimg']);
                        }
                    }
                }
            }

            return JsonModel::init('ok', '')->setRedirect('reload');
        }


        // print_r($this->models->customer->getCustomerInfo(sprintf("customer_id = '%s'", $this->customerId)));die;
        return array(
            'info' => $this->models->customer->getCustomerInfo(sprintf("customer_id = '%s'", $this->customerId)),
        );
    }

    public function cityAction()
    {
        $this->layout->title = '我的常驻地';

        //获取城市
        $sqlInfo = array(
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
            'districtList' => $districtList,
        );
    }

    public function changeCityAction()
    {
        //选择常驻地
        $id = trim($this->param('id'));
        $info = $this->models->district->getDistrictInfo(sprintf("district_id = %d", $id));
        if (!$info) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        //修改常住城市
        $sql = "UPDATE t_customers SET district_id = ? WHERE customer_id = ?";
        $this->locator->db->exec($sql, $id, $this->customerId);

        $sql = "SELECT COUNT(*) 
                FROM t_customer_score_log 
                WHERE customer_id = ? 
                AND score_type = 'have' 
                AND score_des = ?";
        if (!$this->locator->db->getOne($sql, $this->customerId, Score::XZCZD)) {
            //改变积分
            $result = $this->score(array(
                'type' => 'have', 
                'des' => Score::XZCZD, 
                'score' => 10,
            ));
        }

        $this->funcs->redirect($this->helpers->url('customer/info'));
    }

    public function phoneAction()
    {
        $this->layout->title = '绑定手机';

        if ($this->funcs->isAjax()) {
            if (!$_POST['customer_tel']) {
                return new JsonModel('error', '请输入手机号码');
            } elseif (!isPhone($_POST['customer_tel'])) {
                return new JsonModel('error', '手机号码格式错误');
            }
            if (!$_POST['code']) {
                return new JsonModel('error', '请输入验证码');
            }
            $sql = "SELECT * FROM t_phone_verif WHERE phone_number = ?";
            $phoneInfo = $this->locator->db->getRow($sql, trim($_POST['customer_tel']));
            if (!$phoneInfo) {
                return new JsonModel('error', '验证码错误');
            }
            if ($phoneInfo['phone_code'] != trim($_POST['code'])) {
                return new JsonModel('error', '验证码错误');
            }

            //绑定手机
            $sql = "UPDATE t_customers SET customer_tel = ? WHERE customer_id = ?";
            $this->locator->db->exec($sql, trim($_POST['customer_tel']), $this->customerId);

            $sql = "SELECT COUNT(*) 
                    FROM t_customer_score_log 
                    WHERE customer_id = ? 
                    AND score_type = 'have' 
                    AND score_des = ?";
            if (!$this->locator->db->getOne($sql, $this->customerId, Score::BDSJ)) {
                //改变积分
                $result = $this->score(array(
                    'type' => 'have', 
                    'des' => Score::BDSJ, 
                    'score' => 10,
                ));
            }

            return JsonModel::init('ok', '绑定成功')->setRedirect($this->helpers->url('customer/info'));
        }

        return array();
    }

    public function checkInAction()
    {
        $this->layout->title = '签到';
        $this->layout->class = 'calendar';

        $date = date('Y-m-d');

        //连续签到记录
        $sql = "SELECT COUNT(*) FROM t_customer_score_log 
                WHERE customer_id = ? 
                AND score_type = 'have' 
                AND score_des = ?
                AND DATE(log_time) = ?";     
        for($i=0;;$i++) {
            $time = date('Y-m-d', strtotime("{$date} - {$i} days"));
            $status = $this->locator->db->getOne($sql, $this->customerId, Score::YHQD, $time);
            if (!$status && $time != date('Y-m-d')) {
                break;
            }
        }

        //判断今天是否签到
        $sql = "SELECT COUNT(*) FROM t_customer_score_log 
                WHERE customer_id = ? 
                AND score_type = 'have' 
                AND score_des = ?
                AND DATE(log_time) = ?";
        $checkDay = $this->locator->db->getOne($sql, $this->customerId, Score::YHQD, $date);

        //明天获得积分
        if ($checkDay) {
            $scoreNext = (($i + 1) * 1) > 5 ? 5 : (($i + 1) * 1);
        } else {
            $i--;
            $scoreNext = (($i + 2) * 1) > 5 ? 5 : (($i + 2) * 1);
        }

        if ($this->funcs->isAjax()) {
            //签到
            if ($checkDay) {
                return new JsonModel('error', '今天已签到，请明天再来。');
            }

            //改变积分
            $score = ($i + 1) * 1 > 5 ? 5 : ($i + 1) * 1;
            $status = $this->score(array(
                'type' => 'have', 
                'des' => Score::YHQD, 
                'score' => $score,
            ));
            if ($status) {
                return JsonModel::init('ok', '', array('score' => $score, 'i' => $i + 1));
            } else {
                return new JsonModel('error', '签到失败');
            }
        }

        //获取签到记录
        $checkList = $this->checkHistoryAction($date);

        // print_r($i);die;
        return array(
            'days' => $i,
            'scoreNext' => $scoreNext,
            'checkList' => $checkList,
        );
    }

    public function checkHistoryAction($date = '')
    {
        if ($this->funcs->isAjax()) {
            $date = $this->param('date');
            if ($this->param('status') == 'prev') {
                $date = date("Y-m", strtotime($date . " - 1 months"));
            } else {
                $date = date("Y-m", strtotime($date . " + 1 months"));
            }
        } else {
            $date = date("Y-m", strtotime($date));
        }
        
        $start = date('Y-m-01', strtotime($date));
        $end = date('Y-m-01', strtotime($date . " + 1 months"));

        //获取历史签到记录
        $sql = "SELECT DAY(log_time) AS signDay FROM t_customer_score_log 
                WHERE customer_id = ? 
                AND score_type = 'have' 
                AND score_des = ?
                AND DATE(log_time) >= ?
                AND DATE(log_time) < ?";
        $checkList = $this->locator->db->getAll($sql, $this->customerId, Score::YHQD, $start, $end);
        
        if ($this->funcs->isAjax()) {
            return JsonModel::init('ok', '', $checkList);
        } else {
            return $checkList;
        }
    }

    public function scoreAction()
    {
        $status = trim($this->param('status'));
        if (!in_array($status, array('have', 'buy'))) {
            $this->funcs->redirect($this->helpers->url('customer/score', array('status' => 'have')));
        }

        $this->layout->title = '我的积分';

        $limit = array(0, 11);
        $where = array(
            sprintf("customer_id = %d", $this->customerId),
            sprintf("score_type = '%s'", $status),
        );

        if ($this->funcs->isAjax() && $this->param('type') == 'page') {
            $limit[0] = $this->param('pageSize') * $limit[1];
        }

        $sql = "SELECT * 
                FROM t_customer_score_log 
                WHERE " . implode(" AND ", $where) . "
                ORDER BY log_id DESC
                LIMIT " . implode(",", $limit);
        $scoreList = $this->locator->db->getAll($sql);

        if ($this->funcs->isAjax() && $this->param('type') == 'page') {
            if ($scoreList) {
                foreach ($scoreList as $key => $row) {
                    foreach ($row as $ke => $value) {
                        if (!$value) {
                            $scoreList[$key][$ke] = '';
                        }
                    }

                    $scoreList[$key]['score_quantity'] = $status == 'buy' ? (0 - $row['score_quantity']) : $row['score_quantity'];
                }

                return JsonModel::init('ok', '', $scoreList);
            } else {
                return new JsonModel('error', '');
            }
        } else {
            return array(
                'scoreList' => $scoreList,
            );
        }
    }

    public function qrcodeAction()
    {
        require_once VENDOR_DIR . 'autoload.php';

        $this->app = new Application(require_once CONFIG_DIR . 'wechat.config.php');

        $this->layout->title = '我的邀请码';

        $openId = $_SESSION['openid'];

        if ($this->param('type') == 'group') {
            //邀请组团
            $id = trim($this->param('group_id'));
            $info = $this->models->orderGroup->getOrderGroupById($id);
            if (!$info) {
                $this->funcs->redirect($this->helpers->url('default/index'));
            }

            $key = HTTP_SERVER . BASE_PATH . "cart/index?id=" . $info['product_id'] . "&group_id=" . $info['group_id'];
        } else {
            $key = HTTP_SERVER . BASE_PATH . "default/index?key=";
        }

        if ($this->funcs->isAjax()) {
            for($i=0;;$i++) {
                $code = strtoupper(substr($this->funcs->rand(), 0, 8));
                $where = sprintf("customer_invite_code = '%s'", $code);
                if (!$this->models->customer->getCustomerInfo($where)) {
                    break;
                }
            }

            $sql = "UPDATE t_customers SET customer_invite_code = ? WHERE customer_openid = ?";
            $status = $this->locator->db->exec($sql, $code, $openId);
            if ($status) {
                $key .= $code;
                $src = (string) $this->helpers->url('common/qrcode', array('key' => $this->funcs->encrypt($key, 'E', QRCODE_KEY)));

                $shareInfo = array(
                    'title' => '和我一起来白领',
                    'desc' => '白领商品的地方',
                    'link' => $key,
                    'imgUrl' => (string) $this->helpers->image('head_img.jpg', true),
                    'type' => 'link',
                );

                return JsonModel::init('ok', '', array('src' => $src, 'shareInfo' => $shareInfo));
            } else {
                return new JsonModel('error', '刷新失败');
            }
        }

        if (!$this->param('type')) {
            $where = sprintf("customer_openid = '%s'", $openId);
            $info = $this->models->customer->getCustomerInfo($where);

            $key .= $info['customer_invite_code'];

            $shareInfo = array(
                'title' => '和我一起来白领',
                'desc' => '白领商品的地方',
                'link' => $key,
                'imgUrl' => (string) $this->helpers->image('head_img.jpg', true),
                'type' => 'link',
            );
        } else {
            $productInfo = $this->models->product->getProductById($info['product_id']);
            $shareInfo = array(
                'title' => '和我一起来组团',
                'desc' => $productInfo['product_desc'],
                'link' => $key,
                'imgUrl' => (string) $this->helpers->uploadUrl($productInfo['images']['banner'][0]['image_path'], 'product', true),
                'type' => 'link',
            );
        }

    	return array(
    		'key' => $key,
            'js' => $this->app->js,
            'shareInfo' => $shareInfo,
    	);
    }

    public function inviteAction()
    {
        $inviteCode = $this->param('key');
        $where = sprintf("customer_invite_code = '%s'", $inviteCode);
        $info = $this->models->customer->getCustomerInfo($where);

        print_r($info);die;
    }

    public function addressAction()
    {
        $this->layout->title = '我的地址';

        //用户信息
        $info = $this->models->customer->getCustomerInfo(sprintf("customer_id = %d", $this->customerId));

        //收货地址
        $sql = "SELECT c.*, d.district_name FROM t_customer_address c
                LEFT JOIN t_district d ON d.district_id = c.district_id
                WHERE customer_id = ?
                ORDER BY address_id DESC";
        $addressList = $this->locator->db->getAll($sql, $this->customerId);
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
            'addressList' => $addressList,
            'info' => $info,
        );
    }

    public function addressEditAction()
    {
        $id = trim($this->param('id'));

        $this->layout->title = $id ? '编辑地址' : '新建地址';

        $sql = "SELECT c.*, d.district_name FROM t_customer_address c
                LEFT JOIN t_district d ON d.district_id = c.district_id
                WHERE address_id = ?";
        $info = $this->locator->db->getRow($sql, $id);

        if ($this->funcs->isAjax()) {
            if (!$_POST['user_name']) {
                return new JsonModel('error', '请输入姓名');
            }
            if (!$_POST['user_tel']) {
                return new JsonModel('error', '请输入手机号码');
            } elseif (!isPhone($_POST['user_tel'])) {
                return new JsonModel('error', '手机格式错误');
            }
            if (!$_POST['user_address_city']) {
                return new JsonModel('error', '请选择省市区');
            }
            if (!$_POST['user_address_detail']) {
                return new JsonModel('error', '请填写详细地址');
            }

            $addressCity = preg_replace("/[ ]{2,}/", " ", trim($_POST['user_address_city']));
            $addressCity = explode(" ", $addressCity);
            if (count($addressCity) > 1) {
                $city = $addressCity[1];
            } else {
                $city = $addressCity[0];
            }
            $districtInfo = $this->models->district->getDistrictInfo(array(sprintf("district_name LIKE'%s%%'", $city)));

            $map = array(
                'district_id' => $districtInfo['district_id'],
                'user_address' => implode(" ", $addressCity) . '  ' . preg_replace("/[ ]{2,}/", " ", trim($_POST['user_address_detail'])),
                'user_name' => trim($_POST['user_name']),
                'user_tel' => trim($_POST['user_tel']),
            );
            $set = "district_id = :district_id, user_address= :user_address, user_name = :user_name, user_tel = :user_tel";
            if ($info) {
                $map['address_id'] = $id;
                $sql = "UPDATE t_customer_address SET {$set} WHERE address_id = :address_id";
            } else {
                $map['customer_id'] = $this->customerId;
                $set .= ',customer_id = :customer_id';
                $sql = "INSERT INTO t_customer_address SET {$set}";
            }

            $status = $this->locator->db->exec($sql, $map);
            if ($status) {
                return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('customer/address', array('redirect' => $this->param('redirect'), 'prize_id' => $this->param('prize_id'))));
            } else {
                return new JsonModel('error', '提交失败');
            }
        }

        if ($info) {
            $address = explode("  ", $info['user_address']);
            if (count($address) > 1) {
                $info['user_address_city'] = $address[0];
                $info['user_address_detail'] = $address[1];
            } else {
                $info['user_address_city'] = '';
                $info['user_address_detail'] = $address[0];
            }
        }

        // print_r($info);die;
        return array(
            'info' => $info,
        );
    }

    public function collectionAction()
    {
        $this->layout->title = '我的收藏';

        $limit = array(0, 6);
        $where = array(
            sprintf("customer_id = %d", $this->customerId),
        );

        if ($this->funcs->isAjax() && $this->param('type') == 'page') {
            $limit[0] = $this->param('pageSize') * $limit[1];
        }

        $sql = "SELECT *
                FROM t_customer_collections
                WHERE " . implode(" AND ", $where) . "
                ORDER BY collection_id DESC
                LIMIT " . implode(",", $limit);
        $collectionList = $this->locator->db->getAll($sql);
        if ($collectionList) {
            foreach ($collectionList as $key => $row) {
                $collectionList[$key] = $this->models->product->getProductById($row['product_id']);
            }
        }

        // print_r($collectionList);die;
        if ($this->funcs->isAjax() && $this->param('type') == 'page') {
            if ($collectionList) {
                foreach ($collectionList as $key => $row) {
                    foreach ($row as $ke => $value) {
                        if (!$value) {
                            $collectionList[$key][$ke] = '';
                        }
                    }

                    $collectionList[$key]['image_path'] = (string) $this->helpers->uploadUrl($row['images']['banner'][0]['image_path'], 'product');
                    $collectionList[$key]['product_price'] = $this->funcs->showValue($row['product_price']);
                    $collectionList[$key]['url'] = (string) $this->url('product/detail', array('id' => $row['product_id']));
                }

                return JsonModel::init('ok', '', $collectionList);
            } else {
                return new JsonModel('error', '');
            }
        } else {
            return array(
                'collectionList' => $collectionList,
            );
        }
    }

    public function addressDefaultAction()
    {
        if (!$this->funcs->isAjax()) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        $id = trim($this->param('id'));
        $sql = "SELECT * FROM t_customer_address c 
                LEFT JOIN t_district d ON d.district_id = c.district_id 
                WHERE address_id = ?";
        $info = $this->locator->db->getRow($sql, $id);
        if (!$info) {
            return new JsonModel('error', '地址不存在');
        }

        //修改默认地址
        $sql = "UPDATE t_customers 
                SET customer_default_address_id = ? 
                WHERE customer_id = ?";
        $status = $this->locator->db->exec($sql, $id, $this->customerId);
        if ($status) {
            //判断是否为奖品地址
            if ($this->param('prize_id')) {
                $sql = "UPDATE t_prizes 
                        SET prize_customer_name = :prize_customer_name, 
                        district_id = :district_id,
                        district_name = :district_name,
                        prize_address = :prize_address,
                        prize_tel = :prize_tel
                        WHERE prize_id = :prize_id";
                $this->locator->db->exec($sql, array(
                    'prize_customer_name' => $info['user_name'],
                    'district_id' => $info['district_id'],
                    'district_name' => $info['district_name'],
                    'prize_address' => $info['user_address'],
                    'prize_tel' => $info['user_tel'],
                    'prize_id' => trim($this->param('prize_id')),
                ));
            }
            $redirect = $this->param('redirect');
            if ($redirect) {
                $redirect = str_replace("&amp;", "&", $redirect);
                $redirect = $this->funcs->getUrl($redirect);
                $redirect['params']['address'] = true;
                $redirect = $this->funcs->urlInit($redirect);
                return JsonModel::init('ok', '')->setRedirect($redirect);
            } else {
                return JsonModel::init('ok', '');
            }
        } else {
            return new JsonModel('error', '修改失败');
        }
    }

    public function addressDelAction()
    {
        if (!$this->funcs->isAjax()) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        $id = trim($this->param('id'));
        $sql = "DELETE FROM t_customer_address WHERE address_id = ?";
        $status = $this->locator->db->exec($sql, $id);
        if (!$status) {
            return new JsonModel('error', '删除失败');
        }

        $sql = "UPDATE t_customers 
                SET customer_default_address_id = 0 
                WHERE customer_id = ? 
                AND customer_default_address_id = ?";
        $this->locator->db->exec($sql, $this->customerId, $id);

        return JsonModel::init('ok', '');
    }

    public function groupDetailAction()
    {
        require_once VENDOR_DIR . 'autoload.php';

        $this->app = new Application(require_once CONFIG_DIR . 'wechat.config.php');

        $id = trim($this->param('id'));
        $info = $this->models->orderGroup->getOrderGroupById($id);
        if (!$info) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        $this->layout->title = '订单详情';
        $this->layout->style = "background:#FFF;";
        
        $sql = "SELECT * FROM t_orders 
                WHERE product_id = ? 
                AND customer_id = ?";
        $order = $this->locator->db->getRow($sql, $info['product_id'], $this->customerId);
        $order['product'] = $this->models->product->getProductById($info['product_id']);

        //剩余时间
        $info['time'] = (strtotime($info['group_time']) + $order['product']['product_group_time'] * 86400) - time();

        //组团状态
        if (count($info['customer_id']) == $info['group_type']['data']['product_group_num']) {
            $info['status'] = 'success';
        } elseif ($info['time'] > 0) {
            $info['status'] = 'pending';
        } else {
            $info['status'] = 'error';
        }

        //在规定时间内组团成功，系统自动下单
        if ($info['time'] > 0 && $info['status'] == 'success') {
            $sql = "SELECT * FROM t_orders 
                    WHERE order_type = 'group'
                    AND order_status = 1
                    AND customer_id IN (%s)
                    AND product_id = ?";
            $sql = sprintf($sql, $info['customers']);
            $orders = $this->locator->db->getAll($sql, $info['product_id']);
            if ($orders) {
                foreach ($orders as $row) {
                    $sql = "UPDATE t_orders 
                            SET order_type = 'pending', 
                            order_time = now() 
                            WHERE order_id = ?";
                    $status = $this->locator->db->exec($sql, $row['order_id']);
                    if (!$status) {
                        continue;
                    }

                    //判断是否是被邀请的用户第一次下单
                    $customerInfo = $this->models->customer->getCustomerInfo(sprintf("customer_id = %d", $row['customer_id']));
                    if ($customerInfo['customer_invite_id']) {
                        //下单次数
                        $sql = "SELECT COUNT(*) FROM t_orders WHERE customer_id = ?";
                        $orderCount = $this->locator->db->getOne($sql, $row['customer_id']);
                        if (!$orderCount) {
                            //给邀请人50积分奖励
                            $sql = "UPDATE t_customers 
                                    SET customer_score = customer_score + :customer_score 
                                    WHERE customer_id = :customer_id";
                            $this->locator->db->exec($sql, array(
                                'customer_score' => '50',
                                'customer_id' => $customerInfo['customer_invite_id'],
                            ));

                            //记录积分日志
                            $sql = "INSERT INTO t_customer_score_log 
                                    SET customer_id = :customer_id,
                                    score_type = :score_type,
                                    score_des = :score_des,
                                    score_quantity = :score_quantity";
                            $this->locator->db->exec($sql, array(
                                'customer_id' => $customerInfo['customer_invite_id'],
                                'score_type' => 'have',
                                'score_des' => Score::YQHY,
                                'score_quantity' => '50',
                            ));
                        }
                    } 

                    //扣除积分
                    $sql = "UPDATE t_customers 
                            SET customer_score = customer_score - ? 
                            WHERE customer_id = ?";
                    $this->locator->db->exec($sql, $row['product_price'], $row['customer_id']);

                    //记录积分变动
                    $this->score(array(
                        'type' => 'buy', 
                        'des' => Score::GMSP, 
                        'score' => $row['product_price'],
                    ));
                    
                    //扣除商品数量
                    $sql = "UPDATE t_products 
                            SET product_quantity = product_quantity - 1 
                            WHERE product_id = ?";
                    $this->locator->db->exec($sql, $row['product_id']);

                    if ($row['customer_id'] == $info['customer_id'][0]['customer_id']) { //奖励团长50积分
                        $sql = "UPDATE t_customers 
                                SET customer_score = customer_score + :customer_score 
                                WHERE customer_id = :customer_id";
                        $this->locator->db->exec($sql, array(
                            'customer_score' => '50',
                            'customer_id' => $row['customer_id'],
                        ));

                        //记录积分日志
                        $sql = "INSERT INTO t_customer_score_log 
                                SET customer_id = :customer_id,
                                score_type = :score_type,
                                score_des = :score_des,
                                score_quantity = :score_quantity";
                        $this->locator->db->exec($sql, array(
                            'customer_id' => $row['customer_id'],
                            'score_type' => 'have',
                            'score_des' => Score::ZTCG,
                            'score_quantity' => '50',
                        ));
                    }
                }
            }
        }

        $productInfo = $this->models->product->getProductById($info['product_id']);
        $shareInfo = array(
            'title' => '和我一起来组团',
            'desc' => $productInfo['product_desc'],
            'link' => HTTP_SERVER . BASE_PATH . "cart/index?id=" . $info['product_id'] . "&group_id=" . $info['group_id'];
            'imgUrl' => (string) $this->helpers->uploadUrl($productInfo['images']['banner'][0]['image_path'], 'product', true),
            'type' => 'link',
        );

        // print_r($info);die;
        return array(
            'order' => $order,
            'info' => $info,
            'shareInfo' => $shareInfo,
            'js' => $this->app->js,
        );
    }

    public function levelAction()
    {
        $this->layout->title = '123';

        $nextScore = array();
        $historyScore = $this->models->customer->historyScore($this->customerId);

        $sql = "SELECT * FROM t_customer_score_level 
                WHERE level_status = 1 
                ORDER BY level_score ASC";
        $list = $this->locator->db->getAll($sql);
        if ($list) {
            foreach ($list as $key => $row) {
                if ($row['level_id'] == $this->locator->get('Profile')['level_id']) {
                    if (isset($list[$key + 1])) {
                        $nextScore['score'] = $list[$key + 1]['level_score'] - $historyScore;
                        $nextScore['name'] = $list[$key + 1]['level_name'];
                    }
                }
            }
        }

        // print_r($list);die;
        return array(
            'historyScore' => $historyScore,
            'nextScore' => $nextScore,
            'list' => $list,
            'levelColor' => $this->levelColor,
            'levelIcon' => $this->levelIcon,
        );
    }
}
