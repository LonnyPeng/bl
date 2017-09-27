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
        $this->layout->title = '白领首页';

        return array();
    }

    public function redisAction()
    {
    	$locator = $this->locator;
    	$cache = $locator->get('Framework\Cache\Redis');
    	$topMenu = $cache->get('test', function() use($locator) {
    	    return 'lonny';
    	}, 100);

    	return false;
    }

    public function testAction()
    {
        return array();
    }
}
