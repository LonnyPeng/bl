<?php
/**
 * Standard Library
 *
 * @package Framework_View
 */

namespace Framework\View\Helper;

use Framework\View\Renderer\RendererInterface;

interface HelperInterface
{
    /**
     * Set view object
     *
     * @param RendererInterface $renderer
     * @return HelperInterface
     */
    public function setView(RendererInterface $renderer);

    /**
     * @return RendererInterface
     */
    public function getView();
}
