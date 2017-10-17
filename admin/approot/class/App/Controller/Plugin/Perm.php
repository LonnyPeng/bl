<?php

namespace App\Controller\Plugin;

use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;
use Framework\View\Model\JsonModel;

class Perm implements ServiceLocatorAwareInterface
{
    const READ = 1;//00000001 查看
    const EDIT = 2;//00000010 编辑
    const DEL = 4;//00000100 删除
    const IMP = 8;//00001000 导入
    const EXP = 16;//00010000 导出

    protected $permNames = array(
        1   => 'Read',
        2   => 'Edit',
        4   => 'Del',
        8   => 'Imp',
        16  => 'Exp',
    );

    /**
     * @var ServiceLocator
     */
    protected $locator = null;

    /**
     * Determine whether have permission
     *
     * @param string $perm
     * @return boolean
     * @example
     *  $this->has('product_read');
     *  $this->has('product_read', 'product_update');
     */
    public function has($perm)
    {
        if (empty($_SESSION['login_id'])) {
            return false;
        }

        $profile = $this->locator->get('Profile');
        if (isset($_SESSION['login_name']) && $_SESSION['login_name'] == 'admin') {
            return true;
        }

        $helpers = $this->locator->get(HELPER_MANAGER);
        if (isset($_SESSION['member_perm'][$helpers->pageId()])) {
            if (in_array($perm, $_SESSION['member_perm'][$helpers->pageId()])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check permissions.
     * It will automatically do some operation if don't have the privilege.
     *
     * @param string $perm
     * @return true
     * @see Perm::has
     */
    public function check($perm)
    {
        $perms = func_get_args();
        $has = call_user_func_array(array($this, 'has'), $perms);
        if (!$has) {
            $plugins = $this->locator->get(PLUGIN_MANAGER);
            if ($plugins->funcs->isAjax()) {
                echo JsonModel::init('error', '对不起，你权限不够')->serialize();
                exit;
            }

            $helpers = $this->locator->get(HELPER_MANAGER);
            return $plugins->funcs->redirect($helpers->url());
        }
        return true;
    }

    public function getPerm($value)
    {
        $permList = array();
        if ($this->comparePerm($value, self::READ)) {
            $permList[self::READ] = $this->permNames[self::READ];
        }
        if ($this->comparePerm($value, self::EDIT)) {
            $permList[self::EDIT] = $this->permNames[self::EDIT];
        }
        if ($this->comparePerm($value, self::DEL)) {
            $permList[self::DEL] = $this->permNames[self::DEL];
        }
        if ($this->comparePerm($value, self::EXP)) {
            $permList[self::EXP] = $this->permNames[self::EXP];
        }
        if ($this->comparePerm($value, self::IMP)) {
            $permList[self::IMP] = $this->permNames[self::IMP];
        }

        return $permList;
    }

    protected function comparePerm($perm1, $perm2)
    {
        return $perm1 & $perm2;
    }

    public function read()
    {
        return self::READ;
    }
    
    public function edit()
    {
        return self::EDIT;
    }
    
    public function del()
    {
        return self::DEL;
    }
    
    public function exp()
    {
        return self::EXP;
    }
    
    public function imp()
    {
        return self::IMP;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocator $serviceLocator
     * @return Controller
     */
    public function setServiceLocator(ServiceLocator $serviceLocator)
    {
        $this->locator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocator
     */
    public function getServiceLocator()
    {
        return $this->locator;
    }
}