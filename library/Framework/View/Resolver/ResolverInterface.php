<?php
/**
 * Standard Library
 *
 * @package Framework_View
 */

namespace Framework\View\Resolver;

interface ResolverInterface
{
    /**
     * Resolve a template/pattern name to a resource the renderer can consume
     *
     * @param string $name
     * @return mixed
     */
    public function resolve($name);
}
