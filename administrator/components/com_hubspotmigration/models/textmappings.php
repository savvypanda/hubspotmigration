<?php defined('_JEXEC') or die('Restricted Access');

class HubspotmigrationModelTextmappings extends FOFModel {
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function onBeforeSave(&$data, &$table) {
		if(!is_array($data)) $data = (array)$data;
		if($data['is_regex']) {
			ini_set('track_errors', 'on');
			$php_errormsg = '';
			@preg_match($data['regex_from'],'');
			if($php_errormsg) {
				$data['regex_from'] = '/'.$data['regex_from'].'/';
				$php_errormsg = '';
				@preg_match($data['regex_from'],'');
				if($php_errormsg) {
					ini_set('track_errors', 'off');
					return false;
				}
			}
			ini_set('track_errors', 'off');
		}

		return parent::onBeforeSave($data, $table);
	}
}
