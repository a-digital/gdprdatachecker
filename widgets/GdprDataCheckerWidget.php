<?php
/**
 * GDPR Data Checker plugin for Craft CMS
 *
 * GdprDataChecker Widget
 *
 * --snip--
 * Dashboard widgets allow you to display information in the Admin CP Dashboard.  Adding new types of widgets to
 * the dashboard couldn’t be easier in Craft
 *
 * https://craftcms.com/docs/plugins/widgets
 * --snip--
 *
 * @author    Matt Shearing
 * @copyright Copyright (c) 2018 Matt Shearing
 * @link      https://adigital.agency
 * @package   GdprDataChecker
 * @since     1.0.0
 */
namespace Craft;
class GdprDataCheckerWidget extends BaseWidget
{
    /**
     * Returns the name of the widget name.
     *
     * @return mixed
     */
    public function getName()
    {
        return Craft::t('GDPR Data Checker');
    }
    /**
     * getBodyHtml() does just what it says: it returns your widget’s body HTML. We recommend that you store the
     * actual HTML in a template, and load it via craft()->templates->render().
     *
     * @return mixed
     */
    public function getBodyHtml()
    {
        // Include our Javascript & CSS
        craft()->templates->includeCssResource('gdprdatachecker/css/widgets/GdprDataCheckerWidget.css');
        craft()->templates->includeJsResource('gdprdatachecker/js/widgets/GdprDataCheckerWidget.js');
        /* -- Variables to pass down to our rendered template */
        $variables = array();
        $variables['settings'] = $this->getSettings();
        return craft()->templates->render('gdprdatachecker/widgets/GdprDataCheckerWidget_Body', $variables);
    }
    /**
     * Returns how many columns the widget will span in the Admin CP
     *
     * @return int
     */
    public function getColspan()
    {
        return 1;
    }
    /**
     * Defines the attributes that model your Widget's available settings.
     *
     * @return array
     */
    protected function defineSettings()
    {
        return array(
            'someSetting' => array(AttributeType::String, 'label' => 'Some Setting', 'default' => ''),
        );
    }
    /**
     * Returns the HTML that displays your Widget's settings.
     *
     * @return mixed
     */
    public function getSettingsHtml()
    {

/* -- Variables to pass down to our rendered template */

        $variables = array();
        $variables['settings'] = $this->getSettings();
        return craft()->templates->render('gdprdatachecker/widgets/GdprDataCheckerWidget_Settings',$variables);
    }

    /**
     * If you need to do any processing on your settings’ post data before they’re saved to the database, you can
     * do it with the prepSettings() method:
     *
     * @param mixed $settings  The Widget's settings
     *
     * @return mixed
     */
    public function prepSettings($settings)
    {

/* -- Modify $settings here... */

        return $settings;
    }
}