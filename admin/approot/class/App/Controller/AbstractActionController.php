<?php

namespace App\Controller;

use Framework\Controller\AbstractActionController as ActionController;
use Framework\View\Model\ViewModel;
use Framework\Utils\Http;

/**
 * @property \Framework\View\HelperManager|\Hints\HelperManager $helpers The helper manager
 * @property \Framework\Model\ModelManager|\Hints\ModelManager $models The model manager
 *
 * @property Plugin\Funcs $funcs
 * @property Plugin\Perm $perm
 * @method Plugin\Log log($action, $data)
 */
abstract class AbstractActionController extends ActionController
{
    public function init()
    {
        // register helper functions
        $this->helpers
            ->register('funcs', function() {return $this->plugin('funcs');})
            ->register('perm', function() {return $this->plugin('perm');});

        // set default layout
        $this->layout();
        $this->layout
             ->addChild(new ViewModel('layout/includes/header'), '__header')
             ->addChild(new ViewModel('layout/includes/sidebar'), '__sidebar')
             ->addChild(new ViewModel('layout/includes/footer'), '__footer');

        // set breadcrumb
        $this->helpers->breadcrumb->add('首页', $this->helpers->url());

        //生成随机密码
        // $password = $this->funcs->randPassword(8);
        // $data = array($password, $this->password->encrypt($password));
        // print_r($data);die;

         // check login
        if (empty($_SESSION['login_id'])) {
            if ($this->helpers->pageId() != 'account-login') {
                $this->funcs->redirect($this->helpers->url('account/login', array(
                    'redirect' => ($this->funcs->isAjax() || $this->funcs->isPost()) ? null : $this->helpers->selfUrl(null, false)
                )));
            }
        } else {
            $locator = $this->locator;
            $cache = $locator->get('Framework\Cache\Redis');
            $member = $cache->get('member_' . $_SESSION['login_id'], function() use($locator) {
                $models = $locator->get('Framework\Model\ModelManager');
                return $models->member->getMemberById($_SESSION['login_id']);
            }, 600);
            
            $this->locator->setService('Profile', $member);
        }
    }
}
