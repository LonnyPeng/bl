<?php
/**
 * Standard Library
 *
 * @package Framework_View
 */

namespace Framework\View\Model;

use Framework\View\Variables as ViewVariables;

class JsonModel extends ViewModel
{
    /**
     * JSONP callback (if set, wraps the return in a function call)
     *
     * @var string
     */
    protected $jsonpCallback = null;
    
    /**
     * Constructor
     *
     * @param string|null|array|\Traversable $variablesOrStatus
     * @param string $msg
     * @param mixed $data
     * @param int $delay
     */
    public function __construct($variablesOrStatus = null, $msg = null, $data = null, $delay = null)
    {
        // Initializing the variables container
        $this->variables = new ViewVariables();
        
        // set status
        if (is_string($variablesOrStatus)) {
            $this->setVariable('status', $variablesOrStatus);
        }
        // set variables
        elseif ($variablesOrStatus) {
            $this->setVariables($variablesOrStatus, true);
        }
        
        // set msg
        if ($msg) {
            $this->setVariable('msg', (string) $msg);
        }
        
        // set data
        if ($data) {
            $this->setVariable('data', $data);
        }

        // set delay
        if ($delay) {
            $this->setVariable('delay', $delay);
        }
    }

    /**
     * Initializing JsonModel
     *
     * @param string|null|array|\Traversable $variablesOrStatus
     * @param string $msg
     * @param mixed $data
     * @param int $delay
     * @return JsonModel
     */
    public static function init($variablesOrStatus = 'ok', $msg = null, $data = null, $delay = null)
    {
        return new self($variablesOrStatus, $msg, $data, $delay);
    }
    
    /**
     * Set status and message
     *
     * @param string $status enum('ok', 'error', '...')
     * @param string $msg
     * @param mixed $data
     * @return JsonModel
     */
    public function setStatus($status, $msg = null, $data = null)
    {
        $this->setVariable('status', $status);
        if ($msg) {
            $this->setVariable('msg', $msg);
        }
        if ($data) {
            $this->setVariable('data', $data);
        }
        return $this;
    }

    /**
     * Set redirect and the delay seconds
     *
     * @param string $redirect
     * @param int $delay
     * @return JsonModel
     */
    public function setRedirect($redirect, $delay = null)
    {
        $this->setVariable('redirect', (string) $redirect);
        if (null !== $delay) {
            $this->setVariable('delay', (int) $delay);
        }
        return $this;
    }

    /**
     * Set JavaScript what will be executed
     *
     * @param string $script
     * @return JsonModel
     */
    public function setScript($script)
    {
        $this->setVariable('script', $script);
        return $this;
    }

    /**
     * Set callback function, context object and the parameters
     *
     * @param string $callback
     * @param mixed $data
     * @param string $context
     */
    public function setCallback($callback, $data = null, $context = null)
    {
        $this->setVariable('callback', $callback);
        if ($context) {
            $this->setVariable('context', $context);
        }
        if ($data) {
            $this->setVariable('data', $data);
        }
        return $this;
    }
    
    /**
     * Set the JSONP callback function name
     *
     * @param  string $callback
     * @return JsonModel
     */
    public function setJsonpCallback($callback)
    {
        $this->jsonpCallback = $callback;
        return $this;
    }

    /**
     * Serialize to JSON
     *
     * @return string
     */
    public function serialize()
    {
        $variables = (array) $this->getVariables();
// print_r($this);die;
        if ($this->jsonpCallback) {
            return $this->jsonpCallback . '(' . json_encode($variables) . ');';
        }
        return json_encode($variables);
    }
}