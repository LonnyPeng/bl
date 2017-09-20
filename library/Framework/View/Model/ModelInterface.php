<?php
/**
 * Standard Library
 *
 * @package Framework_View
 */

namespace Framework\View\Model;

/**
 * Interface describing a view model.
 *
 * Extends "Countable"; count() should return the number of children attached
 * to the model.
 *
 * Extends "IteratorAggregate"; should allow iterating over children.
 */
interface ModelInterface extends \Countable, \IteratorAggregate
{
    /**
     * Set view variable
     *
     * @param string $name
     * @param mixed $value
     * @return ModelInterface
     */
    public function setVariable($name, $value);

    /**
     * Get a single view variable
     *
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getVariable($name, $default = null);

    /**
     * Set view variables
     *
     * @param array|ArrayAccess $variables
     * @return ModelInterface
     */
    public function setVariables($variables);

    /**
     * Get view variables
     *
     * @return array|ArrayAccess
     */
    public function getVariables();

    /**
     * Set the template to be used by this model
     *
     * @param string $template
     * @return ModelInterface
     */
    public function setTemplate($template);

    /**
     * Get the template to be used by this model
     *
     * @return string
     */
    public function getTemplate();

    /**
     * Add a child model
     *
     * @param ModelInterface $child
     * @param null|string $placeholder Optional; if specified, the "placeholder" to set on the parent
     * @param null|boolean $append Optional; if specified, append to child  with the same placeholder
     * @return ModelInterface
     */
    public function addChild(ModelInterface $child, $placeholder = null, $append = false);

    /**
     * Get child model
     *
     * @param string $placceholder
     * @return ModelInterface
     */
    public function getChild($placceholder);

    /**
     * Return all children.
     *
     * Return specifies an array, but may be any iterable object.
     *
     * @return array
     */
    public function getChildren();

    /**
     * Does the model have any children?
     *
     * @return boolean
     */
    public function hasChildren();

    /**
     * Set the placeholder of parent
     *
     * @param string $placeholder
     * @return ModelInterface
     */
    public function setPlaceholder($placeholder);

    /**
     * Get the placeholder of parent
     *
     * @return string
     */
    public function getPlaceholder();

    /**
     * Set flag indicating whether or not append to child  with the same placeholder
     *
     * @param boolean $append
     * @return ModelInterface
     */
    public function setAppend($append);

    /**
     * Is this append to child with the same placeholder?
     *
     * @return boolean
     */
    public function isAppend();
}
