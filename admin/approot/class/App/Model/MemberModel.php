<?php

namespace App\Model;

class MemberModel extends CommonModel
{
    protected $name = 't_member m';

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
    public function getMember($files = '*', $sqlInfo = array())
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
    public function getMemberById($memberId)
    {
        $sql = "SELECT * FROM $this->name WHERE member_id = ?";
        return $this->locator->db->getRow($sql, $memberId);
    }

    public function getMemberByName($memberName)
    {
        $sql = "SELECT * FROM $this->name WHERE member_name = ?";
        return $this->locator->db->getRow($sql, $memberName);
    }
}
