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
        	$sql = "SELECT COUNT(*) FROM t_prizes WHERE customer_id = ? AND DAY(prize_time) = ?";
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

	//获得旋转信息
	function getRotate($prize_arr) {
		$data=array();
		$option=$_GET;//根据前台的选择更改原定默认概率
		foreach($prize_arr as $k=>&$v) {
			$v['v']=$option[$k];
		}
		$prize=getPrize($prize_arr);//通过概率原理设计函数获得其中一个奖项
		$angle=$prize['angle'];
		shuffle($angle);//打乱角度数组
		$angle=$angle[0];
		$angle_arr=explode('-',$angle);
		$min=$angle_arr[0];
		$max=$angle_arr[1];
		$angle=mt_rand($min,$max);
		$data['angle']=$angle;
		$data['message']=$prize['prize'];
		$data['duration']=mt_rand(2,5)*1000;
		$data['n']=mt_rand(3,6);//为了不那么单调，随机一下转动时间和转动圈数
		echo json_encode($data);
	}
}
