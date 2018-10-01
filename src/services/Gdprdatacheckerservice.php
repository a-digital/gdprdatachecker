<?php
/**
 * GDPR Data Checker plugin for Craft CMS 3.x
 *
 * Run through the database and pull out any information associated with a specified email address
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 Matt Shearing
 */

namespace adigital\gdprdatachecker\services;

use adigital\gdprdatachecker\Gdprdatachecker;

use Craft;
use craft\base\Component;
use craft\db\Query;
use Dompdf\Dompdf;
use Dompdf\Options;
use craft\helpers\FileHelper;

/**
 * GdprdatacheckerService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Matt Shearing
 * @package   Gdprdatachecker
 * @since     1.0.0
 */
class GdprdatacheckerService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Gdprdatachecker::$plugin->gdprdatacheckerService->generateData()
     *
     * @return mixed
     */
    public function generateData($email)
    {
	    $data = [];
	    $member = self::memberData($email);
	    if ($member !== false) {
		    $data["member"] = $member;
	    }
	    $freeform = self::freeformData($email);
	    if ($freeform !== false) {
		    $data["freeform"] = $freeform;
	    }
	    $formbuilder = self::formbuilderData($email);
	    if ($formbuilder !== false) {
		    $data["formbuilder"] = $formbuilder;
	    }
	    $commerce = self::commerceData($email);
	    if ($commerce !== false) {
		    $data["commerce"] = $commerce;
	    }
	    $charge = self::chargeData($email);
	    if ($charge !== false) {
		    $data["charge"] = $charge;
	    }
	    if (isset($data) && (is_array($data) || (is_object($data) && $data instanceof \Countable)) && count($data) > 0) {
	    	return $data;
	    } else {
		    return false;
	    }
	}
	
	/**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Gdprdatachecker::$plugin->gdprdatacheckerService->memberData()
     *
     * @return mixed
     */
	public function memberData($email)
    {   
/*
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (Gdprdatachecker::$plugin->getSettings()->someAttribute) {
        }
*/
        $query = new Query([
			"select" => ["*"],
			"from" => ["{{%users}}"],
			"where" => ["email" => $email],
			"limit" => 1
		]);
		$member = $query->one();
		
		if (isset($member["photoId"]) && $member["photoId"] <> '') {
			$asset = Craft::$app->assets->getAssetById($member["photoId"]);
			$member["photoUrl"] = Craft::$app->assets->getAssetUrl($asset);
			$member["photoPath"] = Craft::$app->assets->getThumbPath($asset, 200);
		}
		
		if (isset($member["id"]) && $member["id"] <> '') {
			$query = new Query([
				"select" => ["*"],
				"from" => ["{{%entries}}"],
				"where" => ["authorId" => $member["id"]]
			]);
			$entries = $query->all();
			
			$authored = [];
			if (isset($entries) && (is_array($entries) || (is_object($entries) && $entries instanceof \Countable)) && count($entries) > 0) {
				foreach($entries as $entry) {
					$authored[] = Craft::$app->entries->getEntryById($entry["id"]);
				}
			}
			$member["entries"] = $authored;
			
			
			$memberFields = [];
			
			$query = new Query([
				"select" => ["*"],
				"from" => ["{{%content}}"],
				"where" => ["id" => $member["id"]]
			]);
			$fields = $query->one();
		    if (isset($fields) && (is_array($fields) || (is_object($fields) && $fields instanceof \Countable)) && count($fields) > 0) {
			    foreach($fields as $fieldkey => $fieldvar) {
				    if (isset($fieldvar) && $fieldvar <> "" && strpos($fieldkey, "field_") !== false) {
				    	$memberFields[str_replace("field_", "", $fieldkey)] = $fieldvar;
				    }
			    }
		    }
		    $member["fields"] = $memberFields;
		    return $member;
		}

        return false;
    }
    
    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Gdprdatachecker::$plugin->gdprdatacheckerService->freeformData()
     *
     * @return mixed
     */
    public function freeformData($email)
    {
        $tableName = "freeform_fields";
        if (getenv('DB_TABLE_PREFIX') !== "") {
	        $tableName = getenv('DB_TABLE_PREFIX')."_".$tableName;
        }
        $query = new Query([
			"select" => ["count(*)"],
			"from" => ["information_schema.TABLES"],
			"where" => [
				"TABLE_SCHEMA" => getenv('DB_DATABASE'),
				"TABLE_NAME" => $tableName
			],
			"limit" => 1
		]);
		$exists = $query->one();
		if ($exists["count(*)"] === "0") {
			return false;
		}
		
		$query = new Query([
			"select" => ["*"],
			"from" => ["{{%freeform_fields}}"]
		]);
		$fieldData = $query->all();
		
		$fields = [];
		if (isset($fieldData) && (is_array($fieldData) || (is_object($fieldData) && $fieldData instanceof \Countable)) && count($fieldData) > 0) {
			foreach($fieldData as $key => $field) {
				$fields[$field["id"]] = $field["label"];
			}
		}
		
		$tableName = "freeform_submissions";
        if (getenv('DB_TABLE_PREFIX') !== "") {
	        $tableName = getenv('DB_TABLE_PREFIX')."_".$tableName;
        }
		$query = new Query([
			"select" => ["COLUMN_NAME"],
			"from" => ["information_schema.COLUMNS"],
			"where" => [
				"TABLE_SCHEMA" => getenv('DB_DATABASE'),
				"TABLE_NAME" => $tableName
			]
		]);
		$columns = $query->all();
		$submissionQuery = new Query([
			"select" => ["*"],
			"from" => ["{{%freeform_submissions}}"]
		]);
		
		$queryColumns = ["or"];
		if (isset($columns) && (is_array($columns) || (is_object($columns) && $columns instanceof \Countable)) && count($columns) > 0) {
			foreach($columns as $column) {
				if (strpos($column["COLUMN_NAME"], "field_") !== false) {
// 					$submissionQuery->orWhere(["like", $column["COLUMN_NAME"], "%".$email."%"]);
					$queryColumns[] = $column["COLUMN_NAME"]." LIKE '%".$email."%'";
				}
			}
		}
		$submissionQuery->where($queryColumns);
		$results = $submissionQuery->all();
		
		$submissions = [];
		if (isset($results) && (is_array($results) || (is_object($results) && $results instanceof \Countable)) && count($results) > 0) {
			foreach($results as $key => $submission) {
				if (isset($submission) && (is_array($submission) || (is_object($submission) && $submission instanceof \Countable)) && count($submission) > 0) {
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
			return $submissions;
		}
		
        return false;
    }
    
    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Gdprdatachecker::$plugin->gdprdatacheckerService->formbuilderData()
     *
     * @return mixed
     */
    public function formbuilderData($email)
    {
        $tableName = "formbuilder2_entries";
        if (getenv('DB_TABLE_PREFIX') !== "") {
	        $tableName = getenv('DB_TABLE_PREFIX')."_".$tableName;
        }
        $query = new Query([
			"select" => ["count(*)"],
			"from" => ["information_schema.TABLES"],
			"where" => [
				"TABLE_SCHEMA" => getenv('DB_DATABASE'),
				"TABLE_NAME" => $tableName
			],
			"limit" => 1
		]);
		$exists = $query->one();
		if ($exists["count(*)"] === "0") {
			return false;
		}
        
        $query = new Query([
			"select" => ["*"],
			"from" => ["{{%formbuilder2_entries}}"],
			"where" => ["like", "submission", [$email]]
		]);
		$submissions = $query->all();
		
		if (isset($submissions) && (is_array($submissions) || (is_object($submissions) && $submissions instanceof \Countable)) && count($submissions) > 0) {
			foreach($submissions as $key => $submission) {
				$submissions[$key]["submission"] = (array)json_decode($submission["submission"]);
			}
			return $submissions;
		}
		
		return false;
    }
    
    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Gdprdatachecker::$plugin->gdprdatacheckerService->commerceData()
     *
     * @return mixed
     */
    public function commerceData($email)
    {
	    $tableName = "commerce_orders";
        if (getenv('DB_TABLE_PREFIX') !== "") {
	        $tableName = getenv('DB_TABLE_PREFIX')."_".$tableName;
        }
	    $query = new Query([
			"select" => ["count(*)"],
			"from" => ["information_schema.TABLES"],
			"where" => [
				"TABLE_SCHEMA" => getenv('DB_DATABASE'),
				"TABLE_NAME" => $tableName
			],
			"limit" => 1
		]);
		$exists = $query->one();
		if ($exists["count(*)"] === "0") {
			return false;
		}
		
		$query = new Query([
			"select" => ["customerId"],
			"from" => ["{{%commerce_orders}}"],
			"where" => ["email" => $email],
			"groupBy" => ["customerId"]
		]);
		$customers = $query->all();
		
		if (isset($customers) && (is_array($customers) || (is_object($customers) && $customers instanceof \Countable)) && count($customers) > 0) {
			$customer_addresses = "commerce_customers_addresses";
	        if (getenv('DB_TABLE_PREFIX') !== "") {
		        $customer_addresses = getenv('DB_TABLE_PREFIX')."_".$customer_addresses;
	        }
	        $query = new Query([
				"select" => ["count(*)"],
				"from" => ["information_schema.TABLES"],
				"where" => [
					"TABLE_SCHEMA" => getenv('DB_DATABASE'),
					"TABLE_NAME" => $customer_addresses
				],
				"limit" => 1
			]);
			$customer_addresses_exist = $query->one();
	        $commerce_addresses = "commerce_addresses";
	        if (getenv('DB_TABLE_PREFIX') !== "") {
		        $commerce_addresses = getenv('DB_TABLE_PREFIX')."_".$commerce_addresses;
	        }
	        $query = new Query([
				"select" => ["count(*)"],
				"from" => ["information_schema.TABLES"],
				"where" => [
					"TABLE_SCHEMA" => getenv('DB_DATABASE'),
					"TABLE_NAME" => $commerce_addresses
				],
				"limit" => 1
			]);
			$commerce_addresses_exists = $query->one();
			foreach($customers as $key => $customer) {
				$customerRecord = new \craft\commerce\records\Customer();
				$customer[$key]["customer"] = $customerRecord::findOne($customer["customerId"]);
				if ($customer_addresses_exist["count(*)"] !== "0" && $commerce_addresses_exists["count(*)"] !== "0") {
				    $addressRecord = new \craft\commerce\records\CustomerAddress();
				    $records = $addressRecord::find()->where([
		                'customerId' => $customer["customerId"]
		            ])->all();
				    foreach($records as $addressId) {
					    $address = new \craft\commerce\records\Address();
					    $customers[$key]["addresses"][] = $address::find()->where([
			                'id' => $addressId["addressId"]
			            ])->one();
		            }
			    }
			    $order = new \craft\commerce\elements\Order();
	            $query = $order::find();
		        $query->customer($customer[$key]["customer"]);
		        $query->isCompleted(true);
		        $query->limit(null);
			    $customers[$key]["orders"] = $query->all();
			    
			    $query = new Query([
					"select" => ["*"],
					"from" => ["{{%commerce_orders}}"],
					"where" => [
						"customerId" => $customer["customerId"],
						"isCompleted" => 0
					]
				]);
				$customers[$key]["inactiveCarts"] = $query->all();
			}
			return $customers;
		}

        return false;
    }
    
    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Gdprdatachecker::$plugin->gdprdatacheckerService->chargeData()
     *
     * @return mixed
     */
    public function chargeData($email)
    {
	    $tableName = "charge_customers";
        if (getenv('DB_TABLE_PREFIX') !== "") {
	        $tableName = getenv('DB_TABLE_PREFIX')."_".$tableName;
        }
	    $query = new Query([
			"select" => ["count(*)"],
			"from" => ["information_schema.TABLES"],
			"where" => [
				"TABLE_SCHEMA" => getenv('DB_DATABASE'),
				"TABLE_NAME" => $tableName
			],
			"limit" => 1
		]);
		$exists = $query->one();
		if ($exists["count(*)"] === "0") {
			return false;
		}
		
		$query = new Query([
			"select" => ["*"],
			"from" => ["{{%charge_customers}}"],
			"where" => ["email" => $email]
		]);
		$customers = $query->all();
		
		$tableName = "charges";
        if (getenv('DB_TABLE_PREFIX') !== "") {
	        $tableName = getenv('DB_TABLE_PREFIX')."_".$tableName;
        }
		$query = new Query([
			"select" => ["count(*)"],
			"from" => ["information_schema.TABLES"],
			"where" => [
				"TABLE_SCHEMA" => getenv('DB_DATABASE'),
				"TABLE_NAME" => $tableName
			],
			"limit" => 1
		]);
		$exists = $query->one();
		if ($exists["count(*)"] !== 0) {
			if (isset($customers) && (is_array($customers) || (is_object($customers) && $customers instanceof \Countable)) && count($customers) > 0) {
				foreach($customers as $key => $customer) {
					$query = new Query([
						"select" => ["*"],
						"from" => ["{{%charges}}"],
						"where" => ["customerId" => $customer["id"]]
					]);
					$customers[$key]["charges"] = $query->all();
					if (isset($customers[$key]["charges"]) && (is_array($customers[$key]["charges"]) || (is_object($customers[$key]["charges"]) && $customers[$key]["charges"] instanceof \Countable)) && count($customers[$key]["charges"]) > 0) {
						foreach($customers[$key]["charges"] as $chargekey => $charge) {
							$customers[$key]["charges"][$chargekey]["request"] = json_decode($charge["request"]);
						}
					}
				}
			}
			return $customers;
		}

        return false;
    }
    
    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Gdprdatachecker::$plugin->gdprdatacheckerService->generateReport()
     *
     * @return mixed
     */
    public function generateReport($email, $variables)
    {
	    $template = 'gdprdatachecker/_pdf';
		$pdfName = Craft::$app->getInfo()->name." Data Report";
        $variables["gdprLogoPath"] = CRAFT_VENDOR_PATH."/adigital/gdprdatachecker/resources/img/gdpr-logo.png";
		$html = Craft::$app->getView()->renderTemplate($template, $variables);
		// Set the config options
		$pathService = Craft::$app->path;
		$dompdfTempDir = $pathService->tempPath.'gdpr_dompdf';
		$dompdfFontCache = $pathService->cachePath.'gdpr_dompdf';
		$dompdfLogFile = $pathService->logPath.'gdpr_dompdf.htm';
		$io = new FileHelper();
		if (!is_dir($dompdfTempDir)) {
			$io->createDirectory($dompdfTempDir);
		}
		if (!is_dir($dompdfFontCache)) {
			$io->createDirectory($dompdfFontCache);
		}
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
		$destination = CRAFT_VENDOR_PATH."/adigital/gdprdatachecker/src/templates/reports/";
	    if (is_dir($destination)) {
			$io->removeDirectory($destination);
	    }
	    $io->createDirectory($destination);
		$io->writeToFile($destination.$pdfName.".pdf", $pdf_gen);
		
		return $pdfName.".pdf";
    }
}