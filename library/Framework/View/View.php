<?php
/**
 * Standard Library
 *
 * @package Framework_View
 */

namespace Framework\View;

use Framework\View\Renderer\RendererInterface;
use Framework\View\Model\ModelInterface;

class View
{
    /**
     * @var RendererInterface
     */
    protected $renderer = null;

    /**
     * Set renderer
     *
     * It is same with <em>getRenderer</em>
     * It should be removed in future
     *
     * @param RendererInterface $renderer
     */
    public function setRender(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Set renderer
     *
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Get renderer
     *
     * @return RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Render view model
     *
     * @param ModelInterface $model
     * @return string
     */
    public function render(ModelInterface $model)
    {
        if ($model->hasChildren()) {
            $this->renderChildren($model);
        }

        $result = $this->getRenderer()->render($model);
        return $result;
    }

    /**
     * Render children of view model
     *
     * @param ModelInterface $model
     * @return void
     */
    protected function renderChildren(ModelInterface $model)
    {
        foreach ($model as $child) {
            $result = $this->render($child);
            $placeholder = $child->getPlaceholder();
            if (!empty($placeholder)) {
                if ($child->isAppend()) {
                    $oldResult = $model->{$placeholder};
                    $model->setVariable($placeholder, $oldResult . $result);
                } else {
                    $model->setVariable($placeholder, $result);
                }
            }
        }
    }
}