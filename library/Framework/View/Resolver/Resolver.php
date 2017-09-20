<?php
/**
 * Standard Library
 *
 * @package Framework_View
 */

namespace Framework\View\Resolver;

class Resolver implements ResolverInterface
{
    /**
     * @var array
     */
    protected $map = array();

    /**
     * @var array
     */
    protected $paths = array();

    /**
     * @var string
     */
    protected $suffix = 'phtml';

    /**
     * Set the default template suffix
     *
     * @param string $suffix
     * @return Resolver
     */
    public function setSuffix($suffix)
    {
        $this->suffix = ltrim($suffix, '.');
        return $this;
    }

    /**
     * Get the default template suffix
     *
     * @return type
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Set template paths
     *
     * @param array $paths
     * @return Resolver
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;
        return $this;
    }

    /**
     * Add a template path
     *
     * @param string $path
     * @return Resolver
     */
    public function addPath($path)
    {
        $this->paths[] = $path;
        return $this;
    }

    /**
     * Add many template paths once
     *
     * @param array $paths
     * @return Resolver
     */
    public function addPaths(array $paths)
    {
        $this->paths = array_merge($this->paths, $paths);
        return $this;
    }

    /**
     * Get template paths
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Set map
     *
     * @param array $map
     * @return Resolver
     */
    public function setMap(array $map)
    {
        $this->map = $map;
        return $this;
    }

    /**
     * Add map
     *
     * @param array $map
     * @return Resolver
     */
    public function addMap(array $map)
    {
        $this->map = array_map($this->map, $map);
        return $this;
    }

    /**
     * Get map
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Retrieve the filesystem path to a view script
     *
     * @param string $name
     * @return string
     * @throws \RuntimeException
     */
    public function resolve($name)
    {
        // resolve map
        if (isset($this->map[$name])) {
            return $this->map[$name];
        }

        // Ensure we have the expected file extension
        $suffix = $this->getSuffix();
        if (pathinfo($name, PATHINFO_EXTENSION) != $suffix) {
            $name .= '.' . $suffix;
        }

        // find the real template file
        $paths = $this->paths;
        for ($i = count($paths) - 1; $i > -1; $i--) {
            $path = $paths[$i];
            $file = new \SplFileInfo($path . $name);
            if ($file->isReadable()) {
                return $file->getRealPath();
            }
        }

        throw new \RuntimeException("Can't resolve template: $name");
        return false;
    }
}

