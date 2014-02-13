<?php defined('_JEXEC') or die('Restricted Access');

class HubspotmigrationModelAuthors extends FOFModel {
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function populateHubspotAuthors() {
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hubspotmigration'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'migration.php');

		$query = 'SELECT DISTINCT hb_author_id FROM #__hubspotmigration_authors';
		$this->_db->setQuery($query);
		$authorslist = $this->_db->loadColumn();

		$data = array(
			'limit' => 200,
		);
		$result = HubspotmigrationHelper::getRequest('/content/api/v2/blog-authors',$data);
		$http_code = HubspotmigrationHelper::getResponseCode();

		if($http_code != 200) return false;

		$result_object = json_decode($result);
		$hb_authors = $result_object->objects;
		if(!empty($hb_authors)) {
			foreach($hb_authors as $author) {
				if(!in_array($author->id, $authorslist)) {
					$author_data = array(
						'hb_author_id' => $author->id,
						'hb_author_name' => $author->full_name
					);
					$this->save($author_data);
				}
			}
		}
		return true;
	}

	public function onBeforeSave(&$data, &$table) {
		if(!is_array($data)) $data = (array) $data;
		if($data['juser_id']) {
			$juser = JFactory::getUser($data['juser_id']);
			$data['juser_name'] = $juser->name;
		}
		if($data['hb_author_id']) {
			require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hubspotmigration'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'migration.php');
			$result = HubspotmigrationHelper::getRequest('/content/api/v2/blog-authors/'.$data['hb_author_id']);
			$http_code = HubspotmigrationHelper::getResponseCode();
			if($http_code != 200) return false;
			$data['hb_author_name'] = json_decode($result)->full_name;
		}
		return parent::onBeforeSave($data, $table);
	}
}
