<?php defined('_JEXEC') or die('Restricted Access');

class HubspotmigrationHelper {
	private static $handle;
	private static $key;
	private static $keytype;
	private static $baseurl;
	private static $loghandle = false;
	private static $logdetails = false;

	private function __construct() {} //making constructor private to prevent instantiation

	public static function init() {
		if(!is_resource(self::$handle)) {
			$params = JComponentHelper::getParams('com_hubspotmigration');
			self::$key = $params->get('api_key');
			self::$keytype = $params->get('api_key_type','hapikey');
			self::$baseurl = 'https://api.hubapi.com';

			self::$handle = curl_init();
			curl_setopt(self::$handle, CURLOPT_RETURNTRANSFER, 1);
			if($params->get('log_api', false)) {
				self::$loghandle = fopen(JPATH_SITE.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'hbmigrate.log','a');
				curl_setopt(self::$handle, CURLOPT_VERBOSE, 1);
				curl_setopt(self::$handle, CURLOPT_STDERR, self::$loghandle);
				self::$logdetails = $params->get('log_details',false);
			}
		}
	}

	public static function close() {
		if(is_resource(self::$handle)) {
			curl_close(self::$handle);
		}
		if(is_resource(self::$loghandle)) {
			fclose(self::$loghandle);
			self::$loghandle = false;
		}
	}

	public static function getResponseCode() {
		if(is_resource(self::$handle)) return curl_getinfo(self::$handle, CURLINFO_HTTP_CODE);
		return false;
	}

	public static function postRequest($url, $data = array()) {
		self::init();
		$requestdata = json_encode($data);
		curl_setopt(self::$handle, CURLOPT_URL, self::$baseurl.$url.'?'.self::$keytype.'='.self::$key);
		curl_setopt(self::$handle, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt(self::$handle, CURLOPT_POSTFIELDS, $requestdata);
		$returnval = curl_exec(self::$handle);
		if(self::$logdetails && is_resource(self::$loghandle)) {
			fwrite(self::$loghandle,'Request Data: '.$requestdata."\nResponse: ".$returnval."\n");
		}
		return $returnval;
	}

	public static function postRequestWithFile($url, $data = array()) {
		self::init();
		$requestdata = $data;
		curl_setopt(self::$handle, CURLOPT_URL, self::$baseurl.$url.'?'.self::$keytype.'='.self::$key);
		curl_setopt(self::$handle, CURLOPT_POST, 1);
		curl_setopt(self::$handle, CURLOPT_POSTFIELDS, $requestdata);
		$returnval = curl_exec(self::$handle);
		if(self::$logdetails && is_resource(self::$loghandle)) {
			fwrite(self::$loghandle,'Request Data: '.$requestdata."\nResponse: ".$returnval."\n");
		}
		return $returnval;
	}

	public static function putRequest($url, $data = array()) {
		self::init();
		//$requestdata = str_replace(array('\"','\/'),array('\\\\"','/'),json_encode($data));
		//$requestdata = str_replace(array('\"'),array('\\\\"'),json_encode($data));
		//$requestdata = str_replace('\/', '/', json_encode($data));
		$requestdata = json_encode($data);
		curl_setopt(self::$handle, CURLOPT_URL, self::$baseurl.$url.'?'.self::$keytype.'='.self::$key);
	        curl_setopt(self::$handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: '.strlen($requestdata)));
		curl_setopt(self::$handle, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt(self::$handle, CURLOPT_POSTFIELDS, $requestdata);
	        curl_setopt(self::$handle, CURLOPT_SSL_VERIFYPEER, 0);

//	$putfile = fopen('php://temp/maxmemory:256000','w');
//	fwrite($putfile, $requestdata);
//	fseek($putfile,0);
		
//	curl_setopt(self::$handle, CURLOPT_PUT, 1);
//	curl_setopt(self::$handle, CURLOPT_INFILE, $putfile);
//	curl_setopt(self::$handle, CURLOPT_INFILESIZE, strlen($requestdata));
		$returnval = curl_exec(self::$handle);

//	fclose($putfile);
		if(self::$logdetails && is_resource(self::$loghandle)) {
			fwrite(self::$loghandle,'Request Data: '.$requestdata."\nResponse: ".$returnval."\n");
		}
		return $returnval;
	}

	public static function getRequest($url, $data = array()) {
		self::init();
		if(is_object($data)) {
			$data = (array) $data;
		} elseif(!is_array($data)) {
			$data = array($data);
		}
		foreach($data as $key => $val) {
			if(is_object($val) || is_array($val) || empty($val)) {
				unset($data[$key]);
			}
		}
		$requestdata = http_build_query(array(self::$keytype => self::$key) + $data);
		curl_setopt(self::$handle, CURLOPT_URL, self::$baseurl.$url.'?'.$requestdata);
		curl_setopt(self::$handle, CURLOPT_HTTPGET, 1);
		$returnval = curl_exec(self::$handle);
		if(self::$logdetails && is_resource(self::$loghandle)) {
			fwrite(self::$loghandle,'Request Data: '.$requestdata."\nResponse: ".$returnval."\n");
		}
		return $returnval;
	}
}
