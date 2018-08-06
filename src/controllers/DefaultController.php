<?php
/**
 * GDPR Data Checker plugin for Craft CMS 3.x
 *
 * Run through the database and pull out any information associated with a specified email address
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 Matt Shearing
 */

namespace adigital\gdprdatachecker\controllers;

use adigital\gdprdatachecker\Gdprdatachecker;

use Craft;
use craft\web\Controller;
use craft\helpers\FileHelper;
use craft\mail\Message;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Matt Shearing
 * @package   Gdprdatachecker
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's actionCheckEmail URL,
     * e.g.: actions/gdprdatachecker/default/check-email
     *
     * @return mixed
     */
    public function actionCheckEmail()
    {
	    $this->requirePostRequest();
	    $request = Craft::$app->getRequest();
	    $email = $request->getParam('emailAddress');
	    
	    $variables = [];
	    $variables["gdprData"] = Gdprdatachecker::$plugin->gdprdatacheckerService->generateData($email);
        $variables["gdprEmail"] = $email;

        return $this->renderTemplate('gdprdatachecker/result', $variables);
    }
    
    /**
     * Handle a request going to our plugin's actionDownloadReport URL,
     * e.g.: actions/gdprdatachecker/default/download-report
     *
     * @return mixed
     */
    public function actionDownloadReport()
    {
	    $this->requirePostRequest();
	    $request = Craft::$app->getRequest();
	    $email = $request->getParam('emailAddress');
	    
	    $variables = [];
		$variables["gdprData"] = Gdprdatachecker::$plugin->gdprdatacheckerService->generateData($email);
        $variables["gdprEmail"] = $email;
        
        $fileName = Gdprdatachecker::$plugin->gdprdatacheckerService->generateReport($email, $variables);
        $filePath = CRAFT_VENDOR_PATH."/adigital/gdprdatachecker/src/templates/reports/";
        
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
		header("Content-Type: application/octet-stream");
		header("Content-Length: " . filesize($filePath.$fileName));
        readfile($filePath.$fileName);
        $io = new FileHelper();
        $io->removeDirectory($filePath);
        
        exit;
        
        return $this->renderTemplate('gdprdatachecker/result', $variables);
    }
    
    /**
     * Handle a request going to our plugin's actionEmailReport URL,
     * e.g.: actions/gdprdatachecker/default/email-report
     *
     * @return mixed
     */
    public function actionEmailReport()
    {
	    $this->requirePostRequest();
	    $request = Craft::$app->getRequest();
	    $emailAddress = $request->getParam('emailAddress');
	    
	    $variables = [];
		$variables["gdprData"] = Gdprdatachecker::$plugin->gdprdatacheckerService->generateData($emailAddress);
        $variables["gdprEmail"] = $emailAddress;
        
        $fileName = Gdprdatachecker::$plugin->gdprdatacheckerService->generateReport($emailAddress, $variables);
        $filePath = CRAFT_VENDOR_PATH."/adigital/gdprdatachecker/src/templates/reports/".$fileName;
        $template = 'gdprdatachecker/emailTemplate';
        
        $settings = Craft::$app->systemSettings->getSettings('email');
        $message = new Message();
        $message->setFrom([$settings['fromEmail'] => $settings['fromName']]);
	    $message->setTo($emailAddress);
	    $message->setSubject('GDPR Data Report from '.Craft::$app->getInfo()->name);
	    $html = Craft::$app->getView()->renderTemplate($template);
	    $message->setHtmlBody($html);
	    $message->setTextBody('Hello, you are receiving this email because you have requested to receive a report of the data we currently hold on you. If you did not request this information, then please disregard this email. We have attached a PDF which contains any member acount information, form submissions, and order information that we hold in our database attached to your email address. If you would like any of this data to be reviewed and/or removed, then please let us know. Regards, '.Craft::$app->getInfo()->name.' '.Craft::getAlias('@web'));
		if (file_exists($filePath)) {
			$message->attach($filePath, [
                'fileName' => $fileName
            ]);
		}
		Craft::$app->mailer->send($message);
        
		return $this->renderTemplate('gdprdatachecker/result', $variables);
    }
}
