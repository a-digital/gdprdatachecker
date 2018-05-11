<?php
/**
 * GDPR Data Checker plugin for Craft CMS
 *
 * GDPR Data Checker Variable
 *
 * --snip--
 * Craft allows plugins to provide their own template variables, accessible from the {{ craft }} global variable
 * (e.g. {{ craft.pluginName }}).
 *
 * https://craftcms.com/docs/plugins/variables
 * --snip--
 *
 * @author    Matt Shearing
 * @copyright Copyright (c) 2018 Matt Shearing
 * @link      https://adigital.agency
 * @package   GdprDataChecker
 * @since     1.0.0
 */

namespace Craft;

class GdprDataCheckerVariable
{
    /**
     * Whatever you want to output to a Twig template can go into a Variable method. You can have as many variable
     * functions as you want.  From any Twig template, call it like this:
     *
     *     {{ craft.gdprDataChecker.exampleVariable }}
     *
     * Or, if your variable requires input from Twig:
     *
     *     {{ craft.gdprDataChecker.exampleVariable(twigValue) }}
     */
    public function exampleVariable($optional = null)
    {
        return "And away we go to the Twig template...";
    }
}