<?php
/**
 * Standard Library
 *
 * @package Framework_Router
 */

namespace Framework\Router;

class RuleParser implements RuleParserInterface
{
    /**
     * The delimiter between the parameters
     *
     * @var char
     */
    protected $uriDelimiter = '-';

    /**
     * @var array
     */
    protected $rules = array();

    /**
     * @var array
     */
    protected $parsedRules = array();

    /**
     * Whether or not be parsed
     *
     * @var boolean
     */
    protected $parsed = false;

    /**
     * Pairs identifier
     *
     * @var string
     */
    protected $pairsIdentifier = '_pairs';

    /**
     * Set URI delimiter
     *
     * @param char $delimiter
     * @return RuleParser
     * @throws \InvalidArgumentException
     */
    public function setUriDelimiter($delimiter)
    {
        if (strlen($delimiter) != 1) {
            throw new \InvalidArgumentException('Error URI delimiter, only accept a character here.');
        }
        $this->uriDelimiter = $delimiter;
        return $this;
    }

    /**
     * Get the URI delimiter
     *
     * @return char
     */
    public function getUriDelimiter()
    {
        return $this->uriDelimiter;
    }

    /**
     * Set pairs identifier
     *
     * @param string $identifier
     * @return Router
     */
    public function setPairsIdentifier($identifier)
    {
        $this->pairsIdentifier = $identifier;
        return $this;
    }

    /**
     * Get pairs identifier
     *
     * @return string
     */
    public function getPairsIdentifier()
    {
        return $this->pairsIdentifier;
    }

    /**
     * Set the router rules
     *
     * @param array $rules
     * @return RuleParser
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Append rules
     *
     * @param array $rules
     * @return RuleParser
     */
    public function appendRules(array $rules)
    {
        $this->rules = array_merge($this->rules, $rules);
        return $this;
    }

    /**
     * Prepend rules
     * The new rules will be executed first.
     *
     * @param array $rules
     * @return RuleParser
     */
    public function prependRules(array $rules)
    {
        $this->rules = array_merge($rules, $this->rules);
        return $this;
    }

    /**
     * Get the router rules
     *
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Get the parsed rules
     * Considering the performacne, it should be cached (APC cache is better).
     *
     * @return array
     */
    public function getParsedRules()
    {
        if (!$this->parsed) {
            $this->parseRules();
        }
        return $this->parsedRules;
    }

    /**
     * To parse all the rules.
     *
     * @return RuleParser
     */
    protected function parseRules()
    {
        // set "parsed"
        $this->parsed = true;

        // parsed rules
        $parsedRules = array();

        // start to parse
        foreach ($this->rules as $path => $rules) {

            if (is_numeric($path)) {
                $path = '*';
            }

            if (!isset($parsedRules[$path])) {
                $parsedRules[$path] = array();
            }
            foreach((array)$rules as $rule) {

                $rule = $this->replacePairsWildcard($rule);
                $template = $this->getTemplate($rule);

                $rule = $this->quoteRule($rule);
                $array = $this->parseStandardRule($rule);

                $pattern = $array['pattern'];
                $parsedRules[$path][$pattern] = array(
                    'template'  => $template,
                    'all_params' => $array['all_params'],
                    'required_params' => $array['required_params'],
                    'array_params' => $array['array_params'],
                );
            }
        }

        $this->parsedRules = $parsedRules;
        return $this;
    }

    /**
     * Get the template string of creating URLs by the human rule
     *
     * @param string $rule
     * @return string
     */
    protected function getTemplate($rule)
    {
        return preg_replace('/<(\w+)[^>]*>/u','<${1}>', $rule);
    }

    /**
     * Replace the pairs wildcard (*) to pairs identifier (_pairs)
     *
     * @param string $rule
     * @return string
     */
    protected function replacePairsWildcard($rule)
    {
        return str_replace('<*', '<' . $this->pairsIdentifier, $rule);
    }

    /**
     * Quote all the normal parts what are outside of the < and >
     *
     * product-list/<M>/<gender:\w*>-<attrs*::\w+>-eyeglasses.html will become to
     * product\-list/<M>/<gender:\w*>\-<attrs*::\w+>\-eyeglasses\.html
     *
     * @param string $rule
     * @return string
     */
    protected function quoteRule($rule)
    {
        $regex = '# [^\>]*(?=\<) | (?<=\>)[^\<]* | ^[^\>\<]*$ #ux';
        $rule = preg_replace_callback($regex, function($matches) {
            return preg_quote($matches[0], '#');
        }, $rule);
        return $rule;
    }

    /**
     * Parse the standard rule
     *
     * @param string $rule
     * @return array [pattern, all_params, required_params, array_params]
     */
    protected function parseStandardRule($rule)
    {
        $allParams = array();
        $requiredParams = array();
        $arrayParams = array();

        // The return array of Regex will be:
        // [1] parameter name
        // [2] required sign & array sign
        // [3] the regex of parameter
        $regex = '# \< (\w+) ([^\:\>]*) (?: \:([^\>]*) )? \> #ux';

        // callback
        $callback = function($matches) use (&$allParams, &$requiredParams, &$arrayParams) {

            // parameter name
            $param = $matches[1];

            // pairs identifier
            if ($param == $this->pairsIdentifier) {
                if (empty($matches[2])) {
                    $matches[2] = '+';
                }
            }

            //
            // Whether or not be required or array paramter:
            //
            //  (notset) not array parameter     required
            //  (?)      not array parameter     not required
            //  (+)      array parameter         required
            //  (*)      array parameter         not required
            //
            $sign = empty($matches[2]) ? '' : $matches[2]{0};
            if ('' === $sign) {
                $isArrayParam = false;
                $isRequiredParam = true;
            } elseif ('+' === $sign) {
                $isArrayParam = true;
                $isRequiredParam = true;
            } elseif ('*' === $sign) {
                $isArrayParam = true;
                $isRequiredParam = false;
            } elseif ('?' === $sign) {
                $isArrayParam = false;
                $isRequiredParam = false;
            } else {
                throw new \RuntimeException("Invalid required or array sign: " . $sign);
                return false;
            }

            // all parameters
            $allParams[] = $param;

            // require parameters
            if ($isRequiredParam) {
                $requiredParams[] = $param;
            }

            // Be not array parameter
            if (!$isArrayParam) {
                // the regex of parameter
                $regex = empty($matches[3]) ? '[^' . preg_quote($this->uriDelimiter, '#') . '\/]*' : $matches[3];
                return '(?<' . $param . '>' . $regex . ')';
            }

            // get the delimiter for array parameters (Sign is * or +)
            $delimiter = $this->uriDelimiter;
            if ($sign && $tmp = substr($matches[2], 1, 1)) {
                $delimiter = $tmp;
            }

            // Get regex of array paramter
            if ($param == $this->pairsIdentifier) {
                $quotedChars = ($delimiter == $this->uriDelimiter) ? $delimiter : ($this->uriDelimiter . $delimiter);
                $quotedChars = preg_quote($quotedChars, '#');
                $keyRegex = empty($matches[3]) ? '[^' . $quotedChars . ']+' : $matches[3];
                $valueRegex = '[^' . $quotedChars . ']*';
                $regex = '(' . $keyRegex . ')' . preg_quote($delimiter, '#') . $valueRegex;
                // array parameteres
                $arrayParams[$param] = array('#' . $regex . '#u', $delimiter, '#' . $keyRegex . '#u');
            }
            else {
                if (empty($matches[3])) {
                    $quotedChars = ($delimiter == $this->uriDelimiter) ? $delimiter : ($this->uriDelimiter . $delimiter);
                    $regex = '[^' . preg_quote($quotedChars, '#') . ']*';
                } else {
                    $regex = $matches[3];
                }
                // array parameteres
                $arrayParams[$param] = array('#' . $regex . '#u', $delimiter);
            }

            // the real regex of parameter
            $regex = '(' . $regex . ')';
            $regex = $regex . '(?:' . preg_quote($delimiter, '#') . $regex . ')*';

            // transform to the real perl regex
            return '(?<' . $param . '>' . $regex . ')';
        };

        // for parsing URL (ignore case)
        $pattern = '#^' . preg_replace_callback($regex, $callback, $rule) . '$#ui';

        // return
        return array(
            'pattern'   => $pattern,
            'all_params' => $allParams,
            'required_params' => $requiredParams,
            'array_params' => $arrayParams,
        );
    }
}
