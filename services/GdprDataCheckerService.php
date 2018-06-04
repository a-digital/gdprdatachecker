<?php
/**
 * GDPR Data Checker plugin for Craft CMS
 *
 * GdprDataChecker Service
 *
 * --snip--
 * All of your plugin’s business logic should go in services, including saving data, retrieving data, etc. They
 * provide APIs that your controllers, template variables, and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 * --snip--
 *
 * @author    A Digital
 * @copyright Copyright (c) 2018 A Digital
 * @link      https://adigital.agency
 * @package   GdprDataChecker
 * @since     1.0.0
 */

namespace Craft;

use Dompdf\Dompdf;
use Dompdf\Options;

class GdprDataCheckerService extends BaseApplicationComponent
{
    /**
     * This function can literally be anything you want, and you can have as many service functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     craft()->gdprDataChecker->exampleService()
     */
    public function generateDate($email)
    {
	    $data = [];
	    
	    $data["member"] = $this->memberData($email);
		$data["freeform"] = $this->freeformData($email);
		$data["formbuilder"] = $this->formbuilderData($email);
		$data["commerce"] = $this->commerceData($email);
		$data["charge"] = $this->chargeData($email);
	    
	    return $data;
    }
    
    public function memberData($email)
    {
	    $query = craft()->db->createCommand();
	    $member = $query->select("*")->from("users")->where(["email" => $email])->limit(1)->queryRow();
		
		if (isset($member["photo"]) && $member["photo"] <> '') {
			$member["photoUrl"] = "/cms/resources/userphotos/".$member["username"]."/200/".$member["photo"];
			$member["photoPath"] = craft()->path->userPhotosPath.$member["username"]."/200/".$member["photo"];
		}
		
		if (isset($member["id"]) && $member["id"] <> '') {
			$query = craft()->db->createCommand();
		    $entries = $query->select("*")->from("entries")->where(["authorId" => $member["id"]])->queryAll();
		    
			$authored = [];
			if (isset($entries) && count($entries) > 0) {
				foreach($entries as $entry) {
					$authored[] = craft()->entries->getEntryById($entry["id"]);
				}
			}
			$member["entries"] = $authored;
		}
		
		$memberFields = [];
		$query = craft()->db->createCommand();
	    $fields = $query->select("*")->from("content")->where(["id" => $member["id"]])->limit(1)->queryRow();
	    if (isset($fields) && count($fields) > 0) {
		    foreach($fields as $fieldkey => $fieldvar) {
			    if (isset($fieldvar) && $fieldvar <> "" && strpos($fieldkey, "field_") !== false) {
			    	$memberFields[str_replace("field_", "", $fieldkey)] = $fieldvar;
			    }
		    }
	    }
	    $member["fields"] = $memberFields;

        return $member;
    }
    
    public function freeformData($email)
    {
        $tables = craft()->db->getSchema()->getTableNames();
		if (!in_array("craft_freeform_submissions", $tables)) {
			return false;
		}
		
		$query = craft()->db->createCommand();
		$fieldData = $query->select("*")->from("freeform_fields")->queryAll();
		
		$fields = [];
		if (isset($fieldData) && count($fieldData) > 0) {
			foreach($fieldData as $key => $field) {
				$fields[$field["id"]] = $field["label"];
			}
		}
		
		$freeformColumns = ["or"];
		$columns = craft()->db->getSchema()->getTables()["craft_freeform_submissions"]->columns;
		$query = craft()->db->createCommand();
		$submissionQuery = $query->select("*")->from("freeform_submissions");
		if (isset($columns) && count($columns) > 0) {
			foreach($columns as $id => $column) {
				if (strpos($id, "field_") !== false) {
					$submissionQuery->orWhere(["like", $id, "%".$email."%"]);
				}
			}
		}
		$results = $submissionQuery->queryAll();
		
		$submissions = [];
		if (isset($results) && count($results) > 0) {
			foreach($results as $key => $submission) {
				if (isset($submission) && count($submission) > 0) {
					foreach($submission as $fieldKey => $fieldVal) {
						$fieldId = str_replace("field_", "", $fieldKey);
						if (isset($fields[$fieldId]) && $fields[$fieldId] <> '') {
							$submissions[$key][$fields[$fieldId]] = $fieldVal;
						} else {
							$submissions[$key][$fieldKey] = $fieldVal;
						}
					}
				}
			}
		}
		
        return $submissions;
    }
    
    public function formbuilderData($email)
    {
        $tables = craft()->db->getSchema()->getTableNames();
		if (!in_array("craft_formbuilder2_entries", $tables)) {
			return false;
		}
		
		$query = craft()->db->createCommand();
	    $submissions = $query->select("*")->from("formbuilder2_entries")->where(["like", "submission", [$email]])->queryAll();
		
		if (isset($submissions) && count($submissions) > 0) {
			foreach($submissions as $key => $submission) {
				$submissions[$key]["submission"] = (array)json_decode($submission["submission"]);
			}
		}

        return $submissions;
    }
    
    public function commerceData($email)
    {
	    $tables = craft()->db->getSchema()->getTableNames();
		if (!in_array("craft_commerce_orders", $tables)) {
			return false;
		}
		
		$query = craft()->db->createCommand();
	    $customers = $query->select("customerId")->from("commerce_orders")->where(["email" => $email])->group("customerId")->queryAll();
		
		if (isset($customers) && count($customers) > 0) {
			foreach($customers as $key => $customer) {
				$customer[$key]["customer"] = craft()->commerce_customers->getCustomerById($customer["customerId"]);
				if (in_array("craft_commerce_customers_addresses", $tables) && in_array("craft_commerce_addresses", $tables)) {
				    $customers[$key]["addresses"] = craft()->commerce_addresses->getAddressesByCustomerId($customer["customerId"]);
			    }
			    $customers[$key]["orders"] = craft()->commerce_orders->getOrdersByCustomer($customer[$key]["customer"]);
			    
			    $query = craft()->db->createCommand();
			    $customers[$key]["inactiveCarts"] = $query->select("*")->from("commerce_orders")->where(["customerId" => $customer["customerId"], "isCompleted" => 0])->queryAll();
			}
		}

        return $customers;
    }
    
    public function chargeData($email)
    {
	    $tables = craft()->db->getSchema()->getTableNames();
		if (!in_array("craft_charge_customers", $tables)) {
			return false;
		}
		
		$query = craft()->db->createCommand();
	    $customers = $query->select("*")->from("charge_customers")->where(["email" => $email])->queryAll();
		
		if (in_array("craft_charges", $tables)) {
			if (isset($customers) && count($customers) > 0) {
				foreach($customers as $key => $customer) {
					$query = craft()->db->createCommand();
				    $customers[$key]["charges"] = $query->select("*")->from("charges")->where(["customerId" => $customer["id"]])->queryAll();
					if (isset($customers[$key]["charges"]) && count($customers[$key]["charges"]) > 0) {
						foreach($customers[$key]["charges"] as $chargekey => $charge) {
							$customers[$key]["charges"][$chargekey]["request"] = json_decode($charge["request"]);
						}
					}
				}
			}
		}

        return $customers;
    }
    
    public function generateReport($email, $variables)
    {
	    $template = 'gdprdatachecker/_pdf';
		$pdfName = craft()->getSiteName()." Data Report";
		// Set Craft to the site template mode
		$templatesService = craft()->templates;
		$oldTemplateMode = $templatesService->getTemplateMode();
		$templatesService->setTemplateMode(TemplateMode::Site);
		if(!$template || !$templatesService->doesTemplateExist($template))
        {
            // Restore the original template mode
            $templatesService->setTemplateMode($oldTemplateMode);

            throw new HttpException(404, 'Template does not exist.');
        };
        $variables["gdrpLogoPath"] = realpath(dirname(dirname(__FILE__)))."/resources/images/gdpr-logo.png";
		$html = $templatesService->render($template, $variables);
		// Set the config options
		$pathService = craft()->path;
		$dompdfTempDir = $pathService->getTempPath().'gdpr_dompdf';
		$dompdfFontCache = $pathService->getCachePath().'gdpr_dompdf';
		$dompdfLogFile = $pathService->getLogPath().'gdpr_dompdf.htm';
		IOHelper::ensureFolderExists($dompdfTempDir);
		IOHelper::ensureFolderExists($dompdfFontCache);
		$options = new Options([
			'tempDir' => $dompdfTempDir,
			'fontCache' => $dompdfFontCache,
			'logOutputFile' => $dompdfLogFile,
			'enableRemote' => true
		]);
		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($html);
		$size = "A4";
		$orientation = "portrait";
		$dompdf->set_paper($size, $orientation);
		$dompdf->render();
		$pdf_gen = $dompdf->output();
		// Restore the original template mode
		$templatesService->setTemplateMode($oldTemplateMode);
		$destination = realpath(dirname(dirname(__FILE__)))."/templates/reports/";
	    if (is_dir($destination)) {
			$it = new \RecursiveDirectoryIterator($destination, \RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new \RecursiveIteratorIterator($it,
			             \RecursiveIteratorIterator::CHILD_FIRST);
			if (isset($files) && count($files) > 0) {
				foreach($files as $file) {
				    if ($file->isDir()){
				        rmdir($file->getRealPath());
				    } else {
				        unlink($file->getRealPath());
				    }
				}
			}
			rmdir($destination);
	    }
	    mkdir($destination);
		file_put_contents($destination.$pdfName.".pdf", $pdf_gen);
		
		return $pdfName.".pdf";
    }
}