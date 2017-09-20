<?php
/**
 * Standard Library
 *
 * @package Framework_Controller
 * @subpackage Framework_Controller_Plugin
 */

namespace Framework\Controller\Plugin;

use Framework\View\Model\ModelInterface;
use Framework\View\Model\ViewModel;

/**
 * Layout Plugin
 * Set or disable a layout
 */
class Layout extends AbstractPlugin
{
    /**
     * @const string
     */
    const LAYOUT_DEFAULT = 'layout/default';

    /**
     * Set a layout for controller
     *
     * @param ModelInterface|false|null $model
     * @return Layout|false
     */
    public function __invoke($model = null)
    {
        // disable layout
        if (false === $model) {
            $this->getController()->setLayout(null);
            return $this;
        }

        // set model
        if ($model instanceof ModelInterface) {
            if (!$model->getTemplate()) {
                $model->setTemplate(self::LAYOUT_DEFAULT);
            }
            $this->getController()->setLayout($model);
            return $this;
        }

        // array
        if (is_array($model)) {
            $_model = new ViewModel($model);
            $_model->setTemplate(self::LAYOUT_DEFAULT);
            $this->getController()->setLayout($_model);
            return $this;
        }

        // string
        if (is_string($model)) {
            $_model = new ViewModel;
            $_model->setTemplate($model);
            $this->getController()->setLayout($_model);
            return $this;
        }

        // use the default model
        if (null === $model || true === $model) {
            $_model = new ViewModel();
            $_model->setTemplate(self::LAYOUT_DEFAULT);
            $this->getController()->setLayout($_model);
            return $this;
        }

        // exception
        throw new \InvalidArgumentException(sprintf(
            'Invalid parameter type: %s', gettype($model)
        ));
        return false;
    }

    /**
     * @return ModelInterface|null
     */
    public function getModel()
    {
        return $this->getController()->getLayout();
    }
}