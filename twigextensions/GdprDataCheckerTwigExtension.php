<?php
/**
 * GDPR Data Checker plugin for Craft CMS
 *
 * GDPR Data Checker Twig Extension
 *
 * --snip--
 * Twig can be extended in many ways; you can add extra tags, filters, tests, operators, global variables, and
 * functions. You can even extend the parser itself with node visitors.
 *
 * http://twig.sensiolabs.org/doc/advanced.html
 * --snip--
 *
 * @author    A Digital
 * @copyright Copyright (c) 2018 A Digital
 * @link      https://adigital.agency
 * @package   GdprDataChecker
 * @since     1.0.0
 */

namespace Craft;

use Twig_Extension;
use Twig_Filter_Method;

class GdprDataCheckerTwigExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'GdprDataChecker';
    }

    /**
     * Returns an array of Twig filters, used in Twig templates via:
     *
     *      {{ 'something' | someFilter }}
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'camelToSpace' => new \Twig_Filter_Method($this, 'camelToSpaceFunction'),
        );
    }

    /**
     * Returns an array of Twig functions, used in Twig templates via:
     *
     *      {% set this = someFunction('something') %}
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'camelToSpace' => new \Twig_Function_Method($this, 'camelToSpaceFunction'),
        );
    }

    /**
     * Our function called via Twig; it can do anything you want
     *
      * @return string
     */
    public function camelToSpaceFunction($text = null)
    {
        $pattern = '/(([A-Z]{1}))/';
		return preg_replace_callback(
			$pattern,
			function ($matches) {return " " .$matches[0];},
			$text
		);
    }
}