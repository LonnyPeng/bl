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
            $model = $locator->get('Framework\Model\ModelManager');
    	    return $model;
    	}, 600);

        print_r($member);
    	return false;
    }
}
