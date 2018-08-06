<?php
/**
 * GDPR Data Checker plugin for Craft CMS 3.x
 *
 * Run through the database and pull out any information associated with a specified email address
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 Matt Shearing
 */

namespace adigital\gdprdatachecker\variables;

use adigital\gdprdatachecker\Gdprdatachecker;

use Craft;

/**
 * GDPR Data Checker Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.gdprDataChecker }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Matt Shearing
 * @package   Gdprdatachecker
 * @since     1.0.0
 */
class GdprdatacheckerVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.gdprdatachecker.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.gdprdatachecker.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function exampleVariable($optional = null)
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }
        return $result;
    }
}
