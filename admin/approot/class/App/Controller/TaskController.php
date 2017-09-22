<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;
use Framework\View\Model\ViewModel;

class TaskController extends AbstractActionController
{
	protected $imgType = array('image/gif', 'image/jpeg', 'image/x-png', 'image/pjpeg', 'image/png');
	protected $imgMaxSize = 1024 * 1024 * 4; //4M

	public function init()
	{
	    parent::init();
	}

	public function readListAction()
	{
		$where = array();
		if ($this->param('read_title')) {
			$where[] = sprintf("read_title LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('read_title'))) . '%'));
		}
		if ($this->param('read_status') === '0' || $this->param('read_status') === '1') {
			$where[] = sprintf("read_status = %d", $this->param('read_status'));
		}

		$count = $this->models->read->getCount(array('setWhere' => $where));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = '*';
		$sqlInfo = array(
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'read_status DESC, read_id DESC',
		);

		$readList = $this->models->read->getRead($files, $sqlInfo);

		// print_r($readList);die;
	    return array(
	    	'readList' => $readList,
	    );
	}

	public function readEditAction()
	{
		$id = $this->param('id');
		$info = array();
		if ($id) {
			$info = $this->models->read->getreadById($id);
		}

		if ($this->funcs->isAjax()) {
			if (!$_POST['read_title']) {
				return new JsonModel('error', '请输入标题');
			}

			if (!$id) {
				if (!isset($_FILES['file'])) {
					return new JsonModel('error', '请上传缩略图');
				} elseif (!in_array($_FILES['file']['type'], $this->imgType)) {
					return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
				} elseif ($_FILES['file']['size'] > $this->imgMaxSize) {
					return new JsonModel('error', '请选择小于4M的图片');
				}
			} else {
				if (isset($_FILES['file'])) {
					if (!in_array($_FILES['file']['type'], $this->imgType)) {
						return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
					} elseif ($_FILES['file']['size'] > $this->imgMaxSize) {
						return new JsonModel('error', '请选择小于4M的图片');
					}
				}
			}

			if (!$_POST['read_text']) {
				return new JsonModel('error', '请输入内容');
			}
			if (!$_POST['read_num']) {
				return new JsonModel('error', '请输入每天阅读次数');
			}
			if (!$_POST['read_score']) {
				return new JsonModel('error', '请输入阅读奖励积分');
			}

			if (!$id) {
				$path = $this->saveReadImg($_FILES['file']);
				if (!$path) {
					return new JsonModel('error', '缩略图保存失败');
				} else {
					//处理图片
					$result = $this->funcs->setImage(SYS_DIR . $path, SYS_DIR);
					if (!$result['status']) {
						return new JsonModel('error', $result['content']);
					} else {
						$path = $result['content'];
					}
				}
			} else {
				if (isset($_FILES['file'])) {
					$path = $this->saveReadImg($_FILES['file']);
					if (!$path) {
						return new JsonModel('error', '缩略图保存失败');
					} else {
						//处理图片
						$result = $this->funcs->setImage(SYS_DIR . $path, SYS_DIR);
						if (!$result['status']) {
							return new JsonModel('error', $result['content']);
						} else {
							$path = $result['content'];
						}
					}
				}
			}

			$map = array(
				'read_title' => trim($_POST['read_title']),
				'read_text' => trim($_POST['read_text']),
				'read_num' => trim($_POST['read_num']),
				'read_score' => trim($_POST['read_score']),
				'read_status' => !$_POST['read_status'] ? 1 : 0,
			);
			$set = "read_title = :read_title,
					read_text = :read_text,
					read_num = :read_num,
					read_score = :read_score,
					read_status = :read_status";

			if (!$id) {
				$map['read_banner'] = $path;
				$set .= ",read_banner = :read_banner";

				$sql = "INSERT INTO t_task_reads SET $set";
			} else {
				$map['read_id'] = $id;
				if (isset($_FILES['file'])) {
					$sql = "SELECT read_banner FROM t_task_reads WHERE read_id = ?";
					$pathOld = $this->locator->db->getOne($sql, $id);

					$map['read_banner'] = $path;
					$set .= ",read_banner = :read_banner";
				}
				
				$sql = "UPDATE t_task_reads SET $set
						WHERE read_id = :read_id";
			}

			$status = $this->locator->db->exec($sql, $map);
			if ($status) {
				if ($id && isset($_FILES['file'])) {
					$this->delImage(SYS_DIR . $pathOld);
				}
				return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('task/read-list'));
			} else {
				return new JsonModel('error', '失败');
			}
		}
		return array(
			'info' => $info,
		);
	}

	public function readDelAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sql = "SELECT read_banner FROM t_task_reads WHERE read_id = ?";
		$path = $this->locator->db->getOne($sql, $id);
		
		$sql = "DELETE FROM t_task_reads WHERE read_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			$this->delImage(SYS_DIR . $path);
			return JsonModel::init('ok', '删除成功');
		} else {
			return new JsonModel('error', '删除失败');
		}
	}

	public function questionListAction()
	{
		$where = array();
		if ($this->param('question_title')) {
			$where[] = sprintf("question_title LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('question_title'))) . '%'));
		}
		if ($this->param('question_status') === '0' || $this->param('question_status') === '1') {
			$where[] = sprintf("question_status = %d", $this->param('question_status'));
		}
		if ($this->param('question_type')) {
			if ($this->param('question_type') > 1) {
				$where[] = "question_answer_num > 1";
			} else {
				$where[] = "question_answer_num = 1";
			}
		}

		$count = $this->models->question->getCount(array('setWhere' => $where));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = '*';
		$sqlInfo = array(
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'question_status DESC, question_id DESC',
		);

		$questionList = $this->models->question->getQuestion($files, $sqlInfo);
		if ($questionList) {
			foreach ($questionList as $key => $row) {
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

				$questionList[$key]['question'] = $question;
			}
		}

		// print_r($questionList);die;
	    return array(
	    	'questionList' => $questionList,
	    );
	}

	public function questionEditAction()
	{
		$id = $this->param('id');
		$info = array();
		if ($id) {
			$info = $this->models->question->getQuestionById($id);
		}

		if ($this->funcs->isAjax()) {
			if (!$id) {
				if (!isset($_FILES['file'])) {
					return new JsonModel('error', '请上传缩略图');
				} elseif (!in_array($_FILES['file']['type'], $this->imgType)) {
					return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
				} elseif ($_FILES['file']['size'] > $this->imgMaxSize) {
					return new JsonModel('error', '请选择小于4M的图片');
				}
			} else {
				if (isset($_FILES['file'])) {
					if (!in_array($_FILES['file']['type'], $this->imgType)) {
						return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
					} elseif ($_FILES['file']['size'] > $this->imgMaxSize) {
						return new JsonModel('error', '请选择小于4M的图片');
					}
				}
			}

			if (!$_POST['question_title']) {
				return new JsonModel('error', '请输入问题描述');
			}
			if (!$_POST['question_a']) {
				return new JsonModel('error', '请输入选项A');
			}
			if (!$_POST['question_b']) {
				return new JsonModel('error', '请输入选项B');
			}
			if (!$_POST['question_answer']) {
				return new JsonModel('error', '请选项正确答案');
			}
			if (!$_POST['question_score']) {
				return new JsonModel('error', '请输入阅读奖励积分');
			}

			if (!$id) {
				$path = $this->saveReadImg($_FILES['file']);
				if (!$path) {
					return new JsonModel('error', '缩略图保存失败');
				} else {
					//处理图片
					$result = $this->funcs->setImage(SYS_DIR . $path, SYS_DIR);
					if (!$result['status']) {
						return new JsonModel('error', $result['content']);
					} else {
						$path = $result['content'];
					}
				}
			} else {
				if (isset($_FILES['file'])) {
					$path = $this->saveReadImg($_FILES['file']);
					if (!$path) {
						return new JsonModel('error', '缩略图保存失败');
					} else {
						//处理图片
						$result = $this->funcs->setImage(SYS_DIR . $path, SYS_DIR);
						if (!$result['status']) {
							return new JsonModel('error', $result['content']);
						} else {
							$path = $result['content'];
						}
					}
				}
			}

			$map = array(
				'question_title' => trim($_POST['question_title']),
				'question_a' => trim($_POST['question_a']),
				'question_b' => trim($_POST['question_b']),
				'question_c' => trim($_POST['question_c']),
				'question_d' => trim($_POST['question_d']),
				'question_e' => trim($_POST['question_e']),
				'question_f' => trim($_POST['question_f']),
				'question_answer' => implode(",", $_POST['question_answer']),
				'question_answer_num' => count($_POST['question_answer']),
				'question_score' => trim($_POST['question_score']),
				'question_status' => !$_POST['question_status'] ? 1 : 0,
			);
			$set = "question_title = :question_title,
					question_a = :question_a,
					question_b = :question_b,
					question_c = :question_c,
					question_d = :question_d,
					question_e = :question_e,
					question_f = :question_f,
					question_answer = :question_answer,
					question_answer_num = :question_answer_num,
					question_score = :question_score,
					question_status = :question_status";

			if (!$id) {
				$map['question_banner'] = $path;
				$set .= ",question_banner = :question_banner";

				$sql = "INSERT INTO t_task_questions SET $set";
			} else {
				$map['question_id'] = $id;
				if (isset($_FILES['file'])) {
					$sql = "SELECT question_banner FROM t_task_questions WHERE question_id = ?";
					$pathOld = $this->locator->db->getOne($sql, $id);

					$map['question_banner'] = $path;
					$set .= ",question_banner = :question_banner";
				}
				
				$sql = "UPDATE t_task_questions SET $set
						WHERE question_id = :question_id";
			}

			$status = $this->locator->db->exec($sql, $map);
			if ($status) {
				if ($id && isset($_FILES['file'])) {
					$this->delImage(SYS_DIR . $pathOld);
				}
				return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('task/question-list'));
			} else {
				return new JsonModel('error', '失败');
			}
		}
		return array(
			'info' => $info,
		);
	}

	protected function saveReadImg($row = array())
	{
		$filename = $row['tmp_name'];
		$dir = SYS_DIR;
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
		if (move_uploaded_file($row['tmp_name'], $uploadFilePath)) {
			return $uploadFile;
		} else {
			return false;
		}
	}

	protected function delImage($path = '')
	{
		if (file_exists($path)) {
			@unlink($path);
		}
	}

	public function turntableListAction()
	{
		$where = array();
		if ($this->param('turntable_title')) {
			$where[] = sprintf("turntable_title LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('turntable_title'))) . '%'));
		}
		if ($this->param('turntable_status') === '0' || $this->param('turntable_status') === '1') {
			$where[] = sprintf("turntable_status = %d", $this->param('turntable_status'));
		}

		$count = $this->models->turntable->getCount(array('setWhere' => $where));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = '*';
		$sqlInfo = array(
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'turntable_status DESC, turntable_id DESC',
		);

		$turntableList = $this->models->turntable->getTurntable($files, $sqlInfo);

		// print_r($turntableList);die;
	    return array(
	    	'turntableList' => $turntableList,
	    );
	}
}