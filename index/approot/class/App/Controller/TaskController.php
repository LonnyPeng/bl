<?php

namespace App\Controller;

use Framework\View\Model\ViewModel;
use Framework\View\Model\JsonModel;
use App\Controller\Plugin\Score;
use EasyWeChat\Foundation\Application;

class TaskController extends AbstractActionController
{
	public $customerId = null;
    private $app = null;

    public function init()
    {
        parent::init();
        $this->customerId = $this->locator->get('Profile')['customer_id'];

        require_once VENDOR_DIR . 'autoload.php';

        $this->app = new Application(require_once CONFIG_DIR . 'wechat.config.php');
    }

    public function indexAction()
    {
        $this->layout->title = '任务中心';

        return array();
    }

    public function scoreAction()
    {
        $this->layout->title = '积分任务';

        //获取阅读列表
        $sql = "SELECT *, 'read' AS type FROM t_task_reads 
                WHERE read_status = 1 
                ORDER BY read_id DESC";
        $readList = (array) $this->locator->db->getAll($sql);

        //获取答题列表
        $sql = "SELECT *, 'question' AS type FROM t_task_questions 
                WHERE task_status = 1 
                ORDER BY task_id DESC";
        $questionList = (array) $this->locator->db->getAll($sql);

        $list = array_merge($readList, $questionList);
        shuffle($list);

        // print_r($list);die;
        return array(
            'list' => $list,
        );
    }

    public function readAction()
    {
        $this->layout->title = '阅读详情';
        $id = trim($this->param('id'));
        $sql = "SELECT * FROM t_task_reads 
                WHERE read_id = ? 
                AND read_status = 1";
        $info = $this->locator->db->getRow($sql, $id);
        if (!$info) {
            $this->funcs->redirect($this->helpers->url('task/score'));
        }

        //判断已答题次数
        $sql = "SELECT COUNT(*) 
                FROM t_task_log 
                WHERE customer_id = :customer_id 
                AND key_id = :key_id 
                AND log_type = 'read'";
        $readCount = $this->locator->db->getOne($sql, array(
            'customer_id' => $this->customerId,
            'key_id' => $id,
        ));

        if ($this->funcs->isAjax()) {
            if ($readCount >= $info['read_num']) {
                return new JsonModel('error', '已参加');
            }

            //获取阅读积分
            $status = $this->score(array(
                'type' => 'have', 
                'des' => Score::YDJF, 
                'score' => $info['read_score'],
            ));

            //记录阅读记录
            $sql = "INSERT INTO t_task_log 
                    SET customer_id = ?, 
                    key_id = ?,
                    log_type = 'read',
                    log_question_answer = ''";
            $this->locator->db->exec($sql, $this->customerId, $id);

            return JsonModel::init('ok', '');
        }

        $shareInfo = array(
            'title' => $info['read_title'],
            'desc' => '',
            'link' => (string) $this->helpers->url('task/read', array('id' => $id)),
            'imgUrl' => (string) $this->helpers->uploadUrl($info['read_banner'], 'sys', true),
            'type' => 'link',
        );

        return array(
            'js' => $this->app->js,
            'info' => $info,
            'readCount' => $readCount,
            'shareInfo' => $shareInfo,
        );
    }

    public function questionAction()
    {
        $this->layout->title = '答题详情';
        $id = trim($this->param('id'));

        $sql = "SELECT * FROM t_task_questions WHERE task_id = ?";
        $info = (array) $this->locator->db->getRow($sql, $id);
        if (!$info) {
            $this->funcs->redirect($this->helpers->url('task/score'));
        }

        //判断已答题次数
        $sql = "SELECT COUNT(*) 
                FROM t_task_log 
                WHERE customer_id = :customer_id 
                AND key_id = :key_id 
                AND log_type = 'question'";
        $questionCount = $this->locator->db->getOne($sql, array(
            'customer_id' => $this->customerId,
            'key_id' => $id,
        ));

        if ($this->funcs->isAjax()) {
            if ($questionCount >= $info['task_num']) {
                return new JsonModel('error', '已参加');
            }
            if (!isset($_POST['data'])) {
                return new JsonModel('error', '请选择答案');
            }

            $data = $_POST['data'];
            $sql = "SELECT * FROM t_questions 
                    WHERE question_id = ? 
                    AND question_status = 1";
            foreach ($data as $key => $row) {
                $result = $this->locator->db->getRow($sql, $row['id']);
                if (!$result) {
                    return new JsonModel('error', '提交失败');
                }

                //判断答案是否正确
                if (!isset($row['data'])) {
                    return new JsonModel('error', sprintf("%d请选择答案", $key + 1));
                }

                // if ($result['question_type'] == 1) {
                //     if (!in_array(implode(",", $row['data']), explode(",", $result['question_answer']))) {
                //         return new JsonModel('error', sprintf("%d答案错误", $key + 1));
                //     }
                // } else {
                //     if (implode(",", $row['data']) != explode(",", $result['question_answer'])) {
                //         return new JsonModel('error', sprintf("%d答案错误", $key + 1));
                //     }
                // }
                
                //
            }

            //获取答题积分
            $status = $this->score(array(
                'type' => 'have', 
                'des' => Score::DTJF, 
                'score' => $info['task_score'],
            ));

            //记录阅读记录
            $sql = "INSERT INTO t_task_log 
                    SET customer_id = ?, 
                    key_id = ?,
                    log_type = 'question',
                    log_question_answer = ?";
            $this->locator->db->exec($sql, $this->customerId, $id, serialize($data));

            return JsonModel::init('ok', '');
        }

        $sql = "SELECT * FROM t_questions WHERE task_id = ?";
        $data = $this->locator->db->getAll($sql, $id);
        if ($data) {
           foreach ($data as $key => $row) {
               $question = array();
               foreach ($row as $ke => $value) {
                   if (!preg_match("/question_[abcdef]{1}$/i", $ke)) {
                       continue;
                   }
                   if ($value) {
                       $ke = preg_replace("/^question_/i", '', $ke);
                       $question[$ke] = $value;
                   }
               }

               $data[$key]['question'] = $question;
           }
        }
        $info['data'] = $data;

        $shareInfo = array(
            'title' => $info['task_title'],
            'desc' => '',
            'link' => (string) $this->helpers->url('task/question', array('id' => $id)),
            'imgUrl' => (string) $this->helpers->uploadUrl($info['task_banner'], 'sys', true),
            'type' => 'link',
        );

        return array(
            'js' => $this->app->js,
            'info' => $info,
            'questionCount' => $questionCount,
            'shareInfo' => $shareInfo,
        );
    }

    public function turntableAction()
    {
        $this->layout->title = '惊喜大转盘';

        //获取积分兑换的次数
        $sql = "SELECT chance_num FROM t_turntable_chance 
        		WHERE chance_status = 1 AND customer_id = ?";
        $chanceNum = $this->locator->db->getOne($sql, $this->customerId);

        $sql = "SELECT * FROM t_turntable 
        		ORDER BY turntable_id DESC 
        		LIMIT 0,1";
        $config = $this->locator->db->getRow($sql);
        if ($config) {
        	$config['turntable_num'] = unserialize($config['turntable_num']);
        	$config['turntable_num'] = $config['turntable_num'][$this->locator->get('Profile')['level_id']];
        	//今天抽奖的次数
        	$sql = "SELECT COUNT(*) FROM t_prizes WHERE customer_id = ? AND DATE(prize_time) = ?";
        	$prizeNum = $this->locator->db->getOne($sql, $this->customerId, date('Y-m-d'));
        	if ($prizeNum > $config['turntable_num']) {
        		$config['chance_num'] = $chanceNum;
        	} else {
        		$config['chance_num'] = $config['turntable_num'] - $prizeNum + $chanceNum;
        	}
        }

        $sql = "SELECT * FROM t_turntable_products 
        		WHERE turntablep_status = 1 
        		ORDER BY turntablep_sort DESC";
        $list = (array) $this->locator->db->getAll($sql);

        if ($this->funcs->isAjax()) {
        	//判断是否有抽奖机会
        	if ($config['chance_num'] < 1) {
        		return new JsonModel('error', '你的抽奖机会已用完');
        	}

        	$sum = array_sum(array_column($list, 'turntablep_probability'));
        	$angle = 0;
        	$l = 360 / count($list);
        	foreach ($list as $key => $row) {
        		$row['turntablep_probability'] = sprintf("%d", $row['turntablep_probability'] / $sum * 100);
        		$row['angle'] = array(sprintf("%d-%d", $angle, $angle + $l));
        		$list[$key] = $row;

        		$angle += $l;
        	}
        	
        	//获取中奖奖品
        	$proSum = 0;
        	foreach($list as $row) {
        		$proSum += $row['turntablep_probability'];
        	}
        	foreach($list as $k => $row) {
        		$randNum = mt_rand(1,$proSum);//随机数
        		if ($randNum <= $row['turntablep_probability']) {
        			$prize = $row;
        		} else {
        			$proSum -= $row['turntablep_probability'];
        		}
        	}

        	//保存奖品
        	$map = array(
        		'customer_id' => $this->customerId,
        		'turntablep_id' => $prize['turntablep_id'],
        	);
        	if ($prize['turntablep_attr'] == 'product') {
        		$map['prize_attr'] = 'pending';
        	} elseif ($prize['turntablep_attr'] == 'score') {
        		$map['prize_attr'] = 'shipped';
        	} else {
        		$map['prize_attr'] = 'received';
        	}
        	$sql = "INSERT INTO t_prizes 
        			SET customer_id = :customer_id,
        			turntablep_id = :turntablep_id,
        			prize_attr = :prize_attr";
        	$status = $this->locator->db->exec($sql, $map);
        	if ($status) {
        		$prizeId = $this->locator->db->lastInsertId();
        		if ($prize['turntablep_attr'] == 'score') {
        			//改变积分
        			$status = $this->score(array(
        				'type' => 'have', 
        				'des' => Score::CJHDJF, 
        				'score' => $prize['turntablep_score'],
        			));

        			if ($status) {
        				$sql = "UPDATE t_prizes 
        						SET prize_attr = 'received' 
        						WHERE prize_id = ? 
        						AND prize_attr = 'shipped'";
        				$status = $this->locator->db->exec($sql, $prizeId);
        			}
        		}
        	}

        	if ($status) { //修改积分兑换的次数
	        	if ($prizeNum + 1 > $config['turntable_num']) { //判断是否是免费的
			   		$sql = "UPDATE t_turntable_chance 
		   				SET chance_num = chance_num - 1 WHERE customer_id = ?";
					$status = $this->locator->db->exec($sql, $this->customerId);
	        	}
        	}

        	if (!$status) {
        		return new JsonModel('error', '抽奖失败');
        	}

        	$angle = $prize['angle'];
        	shuffle($angle);//打乱

        	$angle = $angle[0];
        	$angle_arr = explode('-',$angle);

        	$min = $angle_arr[0] + $l / 4;
        	$max = $angle_arr[1] - $l / 4;
        	$angle = mt_rand($min, $max);
        	$data['angle'] = $angle;
        	$data['message'] = $prize['turntablep_title'];
        	$data['duration'] = mt_rand(2, 5) * 1000;
        	$data['n'] = mt_rand(3,6);
            $data['attr'] = $prize['turntablep_attr'];
            $data['href'] = (string) $this->helpers->url('customer/address', array('redirect' => $this->helpers->SelfUrl(), 'prize_id' => $prizeId));

        	return JsonModel::init('ok', '', $data);
        }

        $shareInfo = array(
            'title' => '惊喜大转盘',
            'desc' => '',
            'link' => (string) $this->helpers->url('task/index'),
            'imgUrl' => (string) $this->helpers->image('logo.png', true),
            'type' => 'link',
        );

        // print_r($this->customerId);die;
        return array(
            'js' => $this->app->js,
        	'config' => $config,
        	'list' => $list,
            'shareInfo' => $shareInfo,
        );
    }

    public function chanceAction()
    {
    	if (!$this->funcs->isAjax()) {
    		$this->funcs->redirect($this->helpers->url('default/index'));
    	}

    	$turntableScore = $this->param('turntable_use_score');

    	//判断当前用户积分是否够用
    	if ($turntableScore > $this->locator->get('Profile')['customer_score']) {
    		return new JsonModel('error', '', array('msg' => '你当前积分不够兑换'));
    	}

    	//改变积分
    	$result = $this->score(array(
    		'type' => 'buy', 
    		'des' => Score::JFDHCJ, 
    		'score' => $turntableScore,
    	));
    	if (!$result) {
    		return new JsonModel('error', '', array('msg' => '兑换失败'));
    	}

    	//增加用户积分兑换的次数
    	$sql = "SELECT chance_id FROM t_turntable_chance 
    			WHERE chance_status = 1 AND customer_id = ?";
    	$chanceId = $this->locator->db->getOne($sql, $this->customerId);
    	if ($chanceId) {
    		$sql = "UPDATE t_turntable_chance 
    				SET chance_num = chance_num + 1 WHERE chance_id = ?";
 			$status = $this->locator->db->exec($sql, $chanceId);
    	} else {
    		$sql = "INSERT INTO t_turntable_chance SET customer_id = ?, chance_num = 1";
    		$status = $this->locator->db->exec($sql, $this->customerId);
    	}

    	if ($status) {
    		return JsonModel::init('ok', '');
    	} else {
    		return new JsonModel('error', '', array('msg' => '兑换失败'));
    	}
    }

    public function chanceListAction()
    {
        $this->layout->title = '我的奖品';

        if ($this->param('address')) {
            $sql = "SELECT * FROM t_customer_address c 
                    LEFT JOIN t_district d ON d.district_id = c.district_id 
                    WHERE address_id = ?";
            $info = $this->locator->db->getRow($sql, trim($this->param('address_id')));
            if ($info) {
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
        }

        $where = array(
            "t.turntablep_attr = 'product'",
            sprintf("p.customer_id = %d", $this->customerId),
            "prize_status = 1",
        );

        $join = array(
            "LEFT JOIN t_turntable_products t ON t.turntablep_id = p.turntablep_id"
        );

        $limit = array(0, 6);

        if ($this->funcs->isAjax() && $this->param('type') == 'page') {
            $limit[0] = $this->param('pageSize') * $limit[1];
        }

        $files = 't.*, p.*';
        $sqlInfo = array(
            'setJoin' => $join,
            'setWhere' => $where,
            'setLimit' => $limit,
            'setOrderBy' => 'prize_id DESC',
        );

        $prizeList = $this->models->prize->getPrize($files, $sqlInfo);

        if ($this->funcs->isAjax() && $this->param('type') == 'page') {
            if ($prizeList) {
                $viewModel = new ViewModel(array('prizeList' => $prizeList), 'task/chance-list-ajax');
                return JsonModel::init('ok', '', array('list' => $this->view->render($viewModel)));
            } else {
                return new JsonModel('error', '');
            }
        } else {
            return array(
                'prizeList' => $prizeList,
            );
        }
    }

    public function shareAction()
    {
        if (!$this->funcs->isAjax()) {
            $this->funcs->redirect($this->helpers->url('default/index'));
        }

        //分享次数
        $sql = "SELECT COUNT(*) FROM t_share_log WHERE customer_id = ? AND DATE(share_time) = ?";
        $shareCount = $this->locator->db->getOne($sql, $this->customerId, date("Y-m-d"));
        if ($shareCount > 2) {
            return new JsonModel('error', '你今天已分享2次，请明天再来分享！');
        }

        //分享增加一次抽奖机会
        $sql = "SELECT chance_id FROM t_turntable_chance 
                WHERE chance_status = 1 AND customer_id = ?";
        $chanceId = $this->locator->db->getOne($sql, $this->customerId);
        if ($chanceId) {
            $sql = "UPDATE t_turntable_chance 
                    SET chance_num = chance_num + 1 WHERE chance_id = ?";
        } else {
            $sql = "INSERT INTO t_turntable_chance SET customer_id = ?, chance_num = 1";
        }
        $status = $this->locator->db->exec($sql, $chanceId);

        if(!$status) {
            return new JsonModel('error', '分享失败');
        }

        //分享日志
        $sql = "INSERT INTO t_share_log SET customer_id = ?";
        $this->locator->db->exec($sql, $this->customerId);

        return JsonModel::init('ok', '分享成功')->setRedirect('reload');
    }
}
