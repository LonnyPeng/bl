<?php

/**
 * Standard Library
 *
 * @package Framework_Router
 */

namespace Framework\Router;

interface RuleParserInterface
{
    /**
     * Set URI delimiter
     *
     * @param char $delimiter
     * @return RuleParserInterface
     * @throws \InvalidArgumentException
     */
    public function setUriDelimiter($delimiter);

    /**
     * Get the URI delimiter
     *
     * @return char
     */
    public function getUriDelimiter();

    /**
     * Set the pairs identifier
     *
     * @param string $identifier
     * @return RuleParserInterface
     */
    public function setPairsIdentifier($identifier);

    /**
     * Get the pairs identifier
     *
     * @return string
     */
    public function getPairsIdentifier();

    /**
     * Set the router rules
     *
     * @param array $rules
     * @return RuleParserInterface
     */
    public function setRules(array $rules);

    /**
     * Get the parsed rules
     * Considering the performacne, it should be cached (APC cache is better).
     *
     * The return array should be like:
     * <code>
     * <xx>-<tags+>-<yy?>-*.html =>
     * array(
     *   'path_1' => array(
     *       'url_regex_1' => array(
     *           'template' => '<xx>-<tags>-<yy>-<_query>.html',
     *           'all_params' => array('xx', 'tags', 'yy'),
     *           'required_params' => array('xx', 'tags', '_query'),
     *           'array_params' => array(
     *               'tags' => array('#[^\-]*#u', '-'),
     *               '_query' => array('#[^\-]*#u', '-'),
     *           ),
     *       )
     *       'url_regex' => ...
     *   ),
     *   ...
     * );
     * </code>
     *
     * @return array
     */
    public function getParsedRules();
}