<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;
use App\Controller\Plugin\Score;

class TaskController extends AbstractActionController
{
	public $customerId = null;

    public function init()
    {
        parent::init();
        $this->customerId = $this->locator->get('Profile')['customer_id'];
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

        return array(
            'info' => $info,
        );
    }

    public function questionAction()
    {
        $this->layout->title = '答题详情';
        $id = trim($this->param('id'));

        $sql = "SELECT * FROM $this->name WHERE task_id = ?";
        $info = (array) $this->locator->db->getRow($sql, $id);
        if (!$info) {
            $this->funcs->redirect($this->helpers->url('task/score'));
        }

        $sql = "SELECT * FROM t_questions WHERE task_id = ?";
        $info['data'] = $this->locator->db->getAll($sql, $id);

        return array(
            'info' => $info,
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

        	$min = $angle_arr[0];
        	$max = $angle_arr[1];
        	$angle = mt_rand($min,$max);
        	$data['angle'] = $angle;
        	$data['message'] = $prize['turntablep_title'];
        	$data['duration'] = mt_rand(2, 5) * 1000;
        	$data['n'] = mt_rand(3,6);

        	return JsonModel::init('ok', '', $data);
        }

        // print_r($config);die;
        return array(
        	'config' => $config,
        	'list' => $list,
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
    		return new JsonModel('error', '你当前积分不够兑换');
    	}

    	//改变积分
    	$result = $this->score(array(
    		'type' => 'buy', 
    		'des' => Score::JFDHCJ, 
    		'score' => $turntableScore,
    	));
    	if (!$result) {
    		return new JsonModel('error', '兑换失败');
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
    		return JsonModel::init('ok', '兑换成功')->setRedirect($this->helpers->url('task/turntable'));
    	} else {
    		return new JsonModel('error', '兑换失败');
    	}
    }

    public function chanceListAction()
    {
        $this->layout->title = '我的奖品';

        return array();
    }
}
