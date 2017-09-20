<?php

namespace App\Model;

class MemberModel extends CommonModel
{
    protected $name = 't_member m';

    public function getMemberByName($memberName)
    {
        $sql = "SELECT * FROM $this->name WHERE member_name = ?";
        return $this->locator->db->getRow($sql, $memberName);
    }
}
