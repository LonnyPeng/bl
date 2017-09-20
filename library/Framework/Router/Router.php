<?php
/**
 * Standard Library
 *
 * @package Framework_Router
 */

namespace Framework\Router;

use Framework\Router\RuleParserInterface;
use Framework\Utils\FrameworkString;

class Router implements RouterInterface
{
    /**#@+
     * Route Modes
     *
     * @var string
     */
    const ROUTE_MODE_URLREWRITE = 'urlrewrite';
    const ROUTE_MODE_PATHINFO = 'pathinfo';
    const ROUTE_MODE_QUERY = 'query';
    /**#@-*/

    /**
     * Set the route mode
     *
     * @var string
     */
    protected $routeMode = self::ROUTE_MODE_URLREWRITE;

    /**
     * It only is avaiable when the route mode is "urlrewrite"
     *
     * @var string
     */
    protected $queryKey = 'u';

    /**
     * The base path of urls
     *
     * @var string
     */
    protected $basePath = '/';

    /**
     * Parse to _GET
     *
     * @var boolean
     */
    protected $parseToGet = true;

    /**
     * Path identifiers
     *
     * @var array
     */
    protected $pathIdentifiers = array('_controller', '_action');

    /**
     * Save the rule parser
     *
     * @var RuleParserInterface
     */
    protected $ruleParser = null;

    /**
     * Constructor
     *
     * @param RuleParserInterface $parser
     * @param boolean $parseToGet
     */
    public function __construct(RuleParserInterface $parser, $parseToGet = true)
    {
        $this->setRuleParser($parser);
        $this->parseToGet = $parseToGet;
    }

    /**
     * Set a rule parser
     *
     * @param RuleParserInterface $parser
     * @return Router
     */
    public function setRuleParser(RuleParserInterface $parser)
    {
        $this->ruleParser = $parser;
        return $this;
    }

    /**
     * Get the rule parser
     *
     * @return RuleParserInterface
     */
    public function getRuleParser()
    {
        return $this->ruleParser;
    }

    /**
     * Set the route mode
     *
     * @param string $mode
     * @return Router
     */
    public function setRouteMode($mode)
    {
        $this->routeMode = $mode;
        return $this;
    }

    /**
     * Get the route mode
     *
     * @return string
     */
    public function getRouteMode()
    {
        return $this->routeMode;
    }

    /**
     * Set the base path of URLs
     *
     * @param string $path
     * @return Router
     */
    public function setBasePath($path)
    {
        $this->basePath = $path;
        return $this;
    }

    /**
     * Get the base path of URLs
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Set the path identifiers
     *
     * @param array $identifiers
     * @return Router
     */
    public function setPathIdentifiers(array $identifiers)
    {
        $this->pathIdentifiers = $identifiers;
        return $this;
    }

    /**
     * Get the path identifiers
     *
     * @return array
     */
    public function getPathIdentifiers()
    {
        return $this->pathIdentifiers;
    }

    /**
     * Get the parent parse path
     *
     * @param string $path
     * @return string|boolean
     */
    public function getParentPath($path)
    {
        if (empty($path)) {
            return '*';
        }
        elseif ('*' === $path) {
            return false;
        }
        elseif ('*' === $path{0}) {
            return '*';
        }

        $segments = explode('/', $path);
        foreach ($segments as $i => $segment) {
            if ('*' === $segment) {
                $i--;
                break;
            }
        }
        $segments[$i] = '*';
        return implode('/', $segments);
    }

    /**
     * Parse the path to an array
     *
     * @param string $path
     * @return array
     */
    public function parsePath($path)
    {
        $segments = explode('/', $path);
        $params = array();
        foreach ($segments as $i => $segment) {
            if ('*' !== $segment && isset($this->pathIdentifiers[$i])) {
                $params[$this->pathIdentifiers[$i]] = $segment;
            }
        }
        return $params;
    }

    /**
     * Get the request URL, which doesn't include the leading '/' and the query string
     * It has been url-decoded.
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getRequestUrl()
    {
        switch ($this->routeMode) {

            case self::ROUTE_MODE_URLREWRITE :
                $requestUrl = strtok($_SERVER['REQUEST_URI'], '?');

                // remove the base path
                $requestUrl = substr($requestUrl, strlen($this->basePath));

                // raw urldecode
                $requestUrl = rawurldecode($requestUrl);

                // break
                break;

            case self::ROUTE_MODE_PATHINFO :
                $requestUrl = strtok($_SERVER['REQUEST_URI'], '?');
                $requestUrl = substr($requestUrl, strlen($_SERVER['SCRIPT_NAME']));
                $requestUrl = ltrim(rawurldecode($requestUrl), '/');

                // break
                break;

            case self::ROUTE_MODE_QUERY :
                if (empty($_GET[$this->queryKey])) {
                    return '';
                }
                $requestUrl = ltrim($_GET[$this->queryKey], '/');

                // break
                break;

            default:
                throw new \RuntimeException("Unknown route mode: " . $this->routeMode);
                return false;
                break;
        }

        // return
        return $requestUrl;
    }

    /**
     * parse URL
     *
     * @param string $requestUrl
     * @return array
     */
    public function parseUrl($requestUrl = null)
    {
        // request URL
        if (null === $requestUrl) {
            $requestUrl = ltrim($this->getRequestUrl(), '/');
        }

        // get the parsing rules
        $rules = $this->getRuleParser()->getParsedRules();

        $params = $_GET;

        foreach ($rules as $path => $pathRules) {

            foreach ($pathRules as $regex => $options) {

                // find the right rule
                if (preg_match($regex, $requestUrl, $matches)) {

                    // pairs identifier
                    $pairsIdentifier = $this->getRuleParser()->getPairsIdentifier();

                    // array params (include parsing the pairs string)
                    foreach ($options['array_params'] as $identifier => $arr) {

                        $itemRegex = $arr[0];

                        if (!empty($matches[$identifier])) {
                            preg_match_all($itemRegex, $matches[$identifier], $tmp);
                            $array = array();
                            foreach ($tmp[0] as $item) {
                                if (!empty($item)) {
                                    $array[] = $item;
                                }
                            }

                            // parse the pairs string
                            if ($identifier == $pairsIdentifier) {
                                foreach ($array as $str) {
                                    preg_match($arr[2], $str, $tmp);
                                    $key = $tmp[0];
                                    $value = substr($str, strlen($key) + 1);
                                    $params[$key] = $value;
                                }
                            }
                            // parse the common array parameters
                            else {
                                $params[$identifier] = $array;
                            }
                        }

                        // unset the array parameter from $matches
                        unset($matches[$identifier]);
                    }

                    // merge $matches
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $params[$key] = $value;
                        }
                    }

                    // merge together
                    $params = array_merge($params, $this->parsePath($path));

                    // break
                    break 2;
                }
            }
        }

        // parse to _GET
        if ($this->parseToGet) {
            $_GET = $params;
        }

        // return
        return $params;
    }

    /**
     * Create URL
     *
     * @param string $path
     * @param array $params
     * @return string
     */
    public function createUrl($path = null, array $params = array())
    {
        // get the creation rules
        $parsedRules = $this->getRuleParser()->getParsedRules();
        $pairsIdentifier = $this->getRuleParser()->getPairsIdentifier();
        $pathParams = $this->parsePath($path);

        // filter $params
        foreach ($params as $param => $value) {
            if (null === $value || array() === $value) {
                unset($params[$param]);
            }
        }

        do {
            if (!isset($parsedRules[$path])) {
                continue;
            }

            // exists the path
            $rules = $parsedRules[$path];

            foreach ($rules as $options) {

                // It doesn't exist some required parameter (except pairs and path identifiers)
                foreach ($options['required_params'] as $name) {
                    if (
                        !array_key_exists($name, $params)
                        && $name != $pairsIdentifier
                        && !in_array($name, $this->getPathIdentifiers())
                    ) {
                        continue 2;
                    }
                }

                // pair array & query array
                $keyRegex = empty($options['array_params'][$pairsIdentifier][2]) ? null : $options['array_params'][$pairsIdentifier][2];
                $pairArray = array();
                $queryArray = array();
                foreach ($params as $name => $value) {
                    if (!in_array($name, $options['all_params'])) {
                        if (
                            $keyRegex
                            && preg_match($keyRegex, $name, $matches)
                            && $matches[0] === $name
                        ) {
                            $pairArray[$name] = $value;
                        } else {
                            $queryArray[$name] = $value;
                        }
                    }
                }

                // The pairs identifier is required, but it does not exist.
                if (!$pairArray && in_array($pairsIdentifier, $options['required_params'])) {
                    continue;
                }

                // add $pairArray to $params
                if ($pairArray) {
                    $params[$pairsIdentifier] = $pairArray;
                }

                // get query string
                $queryString = http_build_query($queryArray);

                // found
                $tr = array();
                $params = array_merge($params, $pathParams);
                foreach ($options['all_params'] as $name) {
                    $search = '<' . $name . '>';
                    if (isset($params[$name])) {

                        // array params
                        if (is_array($params[$name])) {
                            if (!isset($options['array_params'][$name])) {
                                throw new \RuntimeException("Index \"$name\" should not be the array parameter.");
                            }

                            // is query identifier
                            if ($name == $pairsIdentifier) {
                                $array = array();
                                foreach ($params[$name] as $k => $v) {
                                    $array[] = rawurlencode($k);
                                    if (is_array($v)) {
                                        $str = var_export($params, true);
                                        throw new \RuntimeException("Invalid creating URL parameter name: $k, parameters are $str");
                                        $v = $v[0];
                                    }
                                    $array[] = rawurlencode($v);
                                }
                                $value = implode($options['array_params'][$name][1], $array);
                            }
                            // other identifiers
                            else {
                                array_walk($params[$name], function(&$item) {
                                    $item = rawurlencode($item);
                                });
                                $value = implode($options['array_params'][$name][1], $params[$name]);
                            }
                        }

                        // string parameters
                        else {
                            $value = rawurlencode((string) $params[$name]);
                        }

                        $tr[$search] = $value;
                    } else {
                        $tr[$search] = '';
                    }
                }

                // create url
                $url = strtr($options['template'], $tr);

                // return
                switch ($this->getRouteMode()) {
                    case self::ROUTE_MODE_URLREWRITE:
                    case self::ROUTE_MODE_PATHINFO:
                        return $this->basePath . $url . FrameworkString::concat($queryString, '?');
                        break;

                    case self::ROUTE_MODE_QUERY:
                        return $this->basePath . '?' . $this->queryKey . '=' . $url . String::concat($queryString, '&');
                        break;

                    default:
                        throw new \RuntimeException("Invalid route mode: " . $this->getRouteMode());
                        break;
                }
            }
        }
        while ($path = $this->getParentPath($path));

        // throw exception
        throw new \InvalidArgumentException("Invalid path: " . $path);
        return false;
    }
}

