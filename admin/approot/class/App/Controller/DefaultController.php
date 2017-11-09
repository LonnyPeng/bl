<?php

namespace App\Controller;

class DefaultController extends AbstractActionController
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        return array();
    }

    public function redisAction()
    {
    	$locator = $this->locator;
    	$cache = $locator->get('Framework\Cache\Redis');
    	$member = $cache->get('member', function() use($locator) {
            $models = $locator->get('Framework\Model\ModelManager');
    	    return $models->member->getMemberById($_SESSION['login_id']);
    	}, 600);

        print_r($member);
    	return false;
    }
}
