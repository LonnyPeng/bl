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
             ->register('funcs', function() {return $this->plugin('funcs');});

        // set default layout
        $this->layout();
        $this->layout
             ->addChild(new ViewModel('layout/includes/header'), '__header')
             ->addChild(new ViewModel('layout/includes/footer'), '__footer');

        //获取微信用户信息
    }
}
