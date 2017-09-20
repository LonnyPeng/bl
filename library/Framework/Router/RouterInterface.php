<?php
/**
 * Standard Library
 *
 * @package Framework_Router
 */

namespace Framework\Router;

interface RouterInterface
{
    public function createUrl($path, array $params);
    public function parseUrl($requestUrl = null);
}
