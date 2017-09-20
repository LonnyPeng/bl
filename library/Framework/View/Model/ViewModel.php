<?php
/**
 * Standard Library
 *
 * @package Framework_View
 */

namespace Framework\View\Model;

use Framework\View\Variables as ViewVariables;

class ViewModel implements ModelInterface
{
    /**
     * The placeholder of parent
     *
     * @var string
     */
    protected $placeholder = '__content';

    /**
     * Child models
     *
     * @var ViewModel[]
     */
    protected $children = array();

    /**
     * Template to use when rendering this model
     *
     * @var string
     */
    protected $template = null;

    /**
     * View variables
     *
     * @var ViewVariables
     */
    protected $variables;

    /**
     * Is this append to child  with the same placeholder?
     *
     * @var boolean
     */
    protected $append = false;

    /**
     * Constructor
     *
     * @param string|null|array|\Traversable $variablesOrTemplate
     * @param string $template
     */
    public function __construct($variablesOrTemplate = null, $template = null)
    {
        // Initializing the variables container
        $this->variables = new ViewVariables();

        // set template
        if (is_string($variablesOrTemplate)) {
            $this->setTemplate($variablesOrTemplate);
        }
        // set variables
        elseif ($variablesOrTemplate) {
            $this->setVariables($variablesOrTemplate, true);
            if ($template) {
                $this->setTemplate($template);
            }
        }
    }

    /**
     * Set view variable
     *
     * @param string $name
     * @param mixed $value
     * @return ViewModel
     */
    public function setVariable($name, $value)
    {
        if ($value instanceof ModelInterface) {
            $this->addChild($value, $name);
            return $this;
        }

        $this->variables[(string) $name] = $value;
        return $this;
    }

    /**
     * Set referenced view variable
     *
     * @param string $name
     * @param mixed &$value
     * @return ViewModel
     */
    public function setVariableByRef($name, &$value)
    {
        if ($value instanceof ModelInterface) {
            $this->addChild($value, $name);
            return $this;
        }

        $this->variables[(string) $name] = $value;
        return $this;
    }

    /**
     * Get a single view variable
     *
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getVariable($name, $default = null)
    {
        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        }
        return $default;
    }

    /**
     * Set view variables en masse
     *
     * Can be an array or a Traversable + ArrayAccess object.
     *
     * @param array|ArrayAccess|Traversable $variables
     * @param boolean $overwrite
     * @throws \InvalidArgumentException
     * @return ViewModel
     */
    public function setVariables($variables, $overwrite = false)
    {
        if (!is_array($variables) && !$variables instanceof Traversable) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects an array, or Traversable argument; received "%s"',
                __METHOD__,
                (is_object($variables) ? get_class($variables) : gettype($variables))
            ));
        }

        if ($overwrite && count($this->getVariables())) {
            $this->getVariables()->clear();
        }

        foreach ($variables as $key => $value) {
            $this->setVariable($key, $value);
        }

        return $this;
    }

    /**
     * Get view variables
     *
     * @return ViewVariables
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Property overloading: set variable value
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setVariable($name, $value);
    }

    /**
     * Property overloading: get variable value
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!$this->__isset($name)) {
            return null;
        }

        $variables = $this->getVariables();
        return $variables[$name];
    }

    /**
     * Property overloading: do we have the requested variable value?
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        $variables = $this->getVariables();
        return isset($variables[$name]);
    }

    /**
     * Property overloading: unset the requested variable
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        if ($this->__isset($name)) {
            unset($this->variables[$name]);
        }
    }

    /**
     * Set the template to be used by this model
     *
     * @param string $template
     * @return ViewModel
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;
        return $this;
    }

    /**
     * Get the template to be used by this model
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Add a child model
     *
     * @param ModelInterface $child
     * @param null|string $placeholder Optional; if specified, the "placeholder" to set on the parent
     * @param null|boolean $append Optional; if specified, append to child  with the same placeholder
     * @return ViewModel
     */
    public function addChild(ModelInterface $child, $placeholder = null, $append = null)
    {
        $this->children[$placeholder] = $child;
        if (null !== $placeholder) {
            $child->setPlaceholder($placeholder);
        }
        if (null !== $append) {
            $child->setAppend($append);
        }

        return $this;
    }

    /**
     * Get child model
     *
     * @param string $placeholder
     * @return ViewModel
     */
    public function getChild($placeholder)
    {
        return $this->children[$placeholder];
    }

    /**
     * Return all children.
     *
     * Return specifies an array, but may be any iterable object.
     *
     * @return ViewModel[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Does the model have any children?
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return count($this->children) > 0;
    }

    /**
     * Set the placeholder of parent
     *
     * @param string $palceholder
     * @return ViewModel
     */
    public function setPlaceholder($palceholder)
    {
        $this->placeholder = $palceholder;
        return $this;
    }

    /**
     * Get the placeholder of parent
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * Set flag indicating whether or not append to child  with the same placeholder
     *
     * @param boolean $append
     * @return ViewModel
     */
    public function setAppend($append)
    {
        $this->append = (boolean) $append;
        return $this;
    }

    /**
     * Is this append to child  with the same placeholder?
     *
     * @return boolean
     */
    public function isAppend()
    {
        return $this->append;
    }

    /**
     * Return count of children
     *
     * @return int
     */
    public function count()
    {
        return count($this->children);
    }

    /**
     * Get iterator of children
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->children);
    }
}
