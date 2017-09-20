<?php

namespace App\Model;

class NotebookModel extends CommonModel
{
    protected $name = 't_notebooks n';

    /**
     * Get member count
     *
     */
    public function getCount($sqlInfo)
    {
        $sql = "SELECT COUNT(*) FROM $this->name";
        $sql = $this->setSql($sql, $sqlInfo);

        return $this->locator->db->getOne($sql);
    }

    /**
     * Get member
     *
     */
    public function getNotebook($files = '*', $sqlInfo = array())
    {
        $files = $this->setFile($files);
        $sql = "SELECT $files FROM $this->name";
        $sql = $this->setSql($sql, $sqlInfo);

        return $this->locator->db->getAll($sql);
    }

    /**
     * Get member by member_id
     *
     * @param string|integer $memberId
     * @return array
     */
    public function getNotebookById($notebookId)
    {
        $sql = "SELECT * FROM $this->name WHERE notebook_id = ?";
        return $this->locator->db->getRow($sql, $notebookId);
    }
}
