<?php
/**
 * Standard Library
 *
 * @package Framework_View
 * @subpackage Framework_View_Helper
 */

namespace Framework\View\Helper;

use Framework\View\Renderer\RendererInterface;

abstract class AbstractHelper implements HelperInterface
{
    /**
     * @var RendererInterface
     */
    protected $view = null;

    /**
     * Set view object
     *
     * @param RendererInterface $renderer
     * @return HelperInterface
     */
    public function setView(RendererInterface $view)
    {
        $this->view = $view;
    }

    /**
     * @return RendererInterface
     */
    public function getView()
    {
        return $this->view;
    }
}