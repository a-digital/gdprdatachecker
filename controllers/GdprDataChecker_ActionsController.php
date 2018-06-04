<?php
/**
 * GDPR Data Checker plugin for Craft CMS
 *
 * GdprDataChecker Controller
 *
 * --snip--
 * Generally speaking, controllers are the middlemen between the front end of the CP/website and your plugin’s
 * services. They contain action methods which handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering post data, saving it on a model,
 * passing the model off to a service, and then responding to the request appropriately depending on the service
 * method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what the method does (for example,
 * actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 * --snip--
 *
 * @author    A Digital
 * @copyright Copyright (c) 2018 A Digital
 * @link      https://adigital.agency
 * @package   GdprDataChecker
 * @since     1.0.0
 */

namespace Craft;

class GdprDataChecker_ActionsController extends BaseController
{

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = array(
        );

    /**
     * Handle a request going to our plugin's index action URL, e.g.: actions/gdprDataChecker/checkEmail
     */
    public function actionCheckEmail()
    {
	    $this->requirePostRequest();
	    $email = craft()->request->getParam('emailAddress');
	    
	    $variables = [];
		$variables["gdprData"] = craft()->gdprDataChecker->generateData($email);
        $variables["gdprEmail"] = $email;
        
        return $this->renderTemplate('gdprdatachecker/result', $variables);
    }
    
    /**
     * Handle a request going to our plugin's index action URL, e.g.: actions/gdprDataChecker/downloadReport
     */
    public function actionDownloadReport()
    {
	    $this->requirePostRequest();
	    $email = craft()->request->getParam('emailAddress');
	    
	    $variables = [];
		$variables["gdprData"] = craft()->gdprDataChecker->generateData($email);
        $variables["gdprEmail"] = $email;
        
        $fileName = craft()->gdprDataChecker->generateReport($email, $variables);
        $filePath = craft()->path->getPluginsPath()."gdprdatachecker/templates/reports/".$fileName;
        
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
		header("Content-Type: application/octet-stream");
		header("Content-Length: " . filesize($filePath));
        readfile($filePath);
        unlink($filePath);
        
        return $this->renderTemplate('gdprdatachecker/result', $variables);
    }
    
    /**
     * Handle a request going to our plugin's index action URL, e.g.: actions/gdprDataChecker/emailReport
     */
    public function actionEmailReport()
    {
	    $this->requirePostRequest();
	    $emailAddress = craft()->request->getParam('emailAddress');
	    
	    $variables = [];
		$variables["gdprData"] = craft()->gdprDataChecker->generateData($emailAddress);
        $variables["gdprEmail"] = $emailAddress;
        
        $fileName = craft()->gdprDataChecker->generateReport($emailAddress, $variables);
        $filePath = craft()->path->getPluginsPath()."gdprdatachecker/templates/reports/".$fileName;
        
        $emailTemplate = craft()->path->getPluginsPath()."gdprdatachecker/templates/emailTemplate.twig";
        
        $email = new EmailModel();
		$email->toEmail = $emailAddress;
		$email->subject = 'GDPR Data Report from '.craft()->getSiteName();
		$email->htmlBody = file_get_contents($emailTemplate);
		$email->body = 'Hello, you are receiving this email because you have requested to receive a report of the data we currently hold on you. If you did not request this information, then please disregard this email. We have attached a PDF which contains any member acount information, form submissions, and order information that we hold in our database attached to your email address. If you would like any of this data to be reviewed and/or removed, then please let us know. Regards, '.craft()->getSiteName().' '.craft()->getSiteUrl();
		if (file_exists($filePath)) {
			$email->addAttachment($filePath, $fileName);
		}
		craft()->email->sendEmail($email);
        
		return $this->renderTemplate('gdprdatachecker/result', $variables);
    }
}