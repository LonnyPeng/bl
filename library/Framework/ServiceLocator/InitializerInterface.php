<?php
/**
 * Standard Library
 *
 * @package Framework_ServiceLocator
 */

namespace Framework\ServiceLocator;

/**
 * It will automatically run initialize() before get a cached plugin via plugin manager.
 */
interface InitializerInterface
{
    public function initialize();
}