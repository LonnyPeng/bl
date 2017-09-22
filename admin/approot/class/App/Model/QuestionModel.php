<?php

namespace App\Model;

class QuestionModel extends CommonModel
{
	protected $name = 't_task_questions q';

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
	public function getQuestion($files = '*', $sqlInfo = array())
	{
	    $files = $this->setFile($files);
	    $sql = "SELECT $files FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);

	    return $this->locator->db->getAll($sql);
	}

	public function getQuestionById($id)
	{
	    $sql = "SELECT * FROM $this->name WHERE question_id = ?";
	    return $this->locator->db->getRow($sql, $id);
	}
}