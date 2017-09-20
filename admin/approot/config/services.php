<?php
use Framework\Config\Config;
use Framework\ServiceLocator\ServiceLocator;
use Framework\Utils\Http;

return array(
    'services' => array(
    ),

    'invokables' => array(
        'Params'    => array('App\ServiceLocator\Invokable', 'getParams'),
        'Db'        => array('App\ServiceLocator\Invokable', 'getDbInstance'),
        'Db2'       => array('App\ServiceLocator\Invokable', 'getDb2Instance'),

        'FrontController' => 'Framework\Controller\FrontController',
    ),

    'parameters' => array(
    ),
);
