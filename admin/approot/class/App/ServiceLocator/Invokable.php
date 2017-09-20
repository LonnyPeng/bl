<?php

namespace App\ServiceLocator;

use Framework\Db\Pdo;
use Framework\Router\Router;
use Framework\Router\RuleParser;
use Framework\ServiceLocator\ServiceLocator;
use Framework\Console\ArgumentParser;

/**
 * Put all the invokable functions together for locator
 */
final class Invokable
{

    /**
     * Get all the query parameters
     *
     * @param \Framework\ServiceLocator\ServiceLocator $locator
     * @return array
     */
    public static function getParams(ServiceLocator $locator)
    {
        /* @var $ruleParser RuleParser */
        $cache = $locator->get('Framework\Cache\Redis');
        $ruleParser = $cache->get('RuleParser', function() {
            $ruleParser = new RuleParser();
            $ruleParser->setRules(include CONFIG_DIR . 'router.php')->getParsedRules();
            return $ruleParser;
        }, 3600);

        /* @var $router Router */
        $router = new Router($ruleParser);
        $router->setBasePath(BASE_PATH);

        // get cli parameters
        if (PHP_SAPI == 'cli') {
            $params = ArgumentParser::parse($_SERVER['argv']);
        }
        // get parameters
        else {
            $params = $router->parseUrl();
        }

        // add Router service
        $locator->setService('Router', $router);

        // return
        return $params;
    }

    /**
     * Get the primary DB instance
     *
     * @return \Framework\Db\Pdo
     */
    public static function getDbInstance()
    {
        try {
            // get instance
            $db = new Pdo(
                    'mysql:dbname=' . DB_DATABASE . ';host=' . DB_HOST . ';port=' . DB_PORT, DB_USERNAME, DB_PASSWORD
            );
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit;
        }

        $db->query("SET NAMES 'UTF8'");

        // set the default fetch mode
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        // return
        return $db;
    }

    /**
     * Get the second DB instance
     * It can only be used for query
     *
     * @return \Framework\Db\Pdo
     */
    public static function getDb2Instance()
    {
        try {
            // get instance
            $db = new Pdo(
                    'mysql:dbname=' . DB2_DATABASE . ';host=' . DB2_HOST . ';port=' . DB2_PORT, DB2_USERNAME, DB2_PASSWORD
            );
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit;
        }

        $db->query("SET NAMES 'UTF8'");

        // set the default fetch mode
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        // return
        return $db;
    }
}
