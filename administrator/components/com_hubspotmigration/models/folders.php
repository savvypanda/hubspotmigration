<?php defined('_JEXEC') or die('Restricted Access');

class HubspotmigrationModelFolders extends FOFModel {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function primefolders() {
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hubspotmigration'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'migration.php');

		$query = 'SELECT DISTINCT hb_folder_id, jpath FROM #__hubspotmigration_folders';
		$this->_db->setQuery($query);
		$folderslist = array();
		$pathslist = $this->_db->loadObjectList();
		foreach($pathslist as $path) {
			$folderslist[$path->hb_folder_id] = $path->jpath;
		}

		$limit = 200;
		$limitstart = 0;

		do {
			$data = array(
				'limit' => $limit,
				'limitstart' => $limitstart
			);
			$result = HubspotmigrationHelper::getRequest('/content/api/v2/folders',$data);
			$http_code = HubspotmigrationHelper::getResponseCode();

			if($http_code != 200) return false;

			$result_object = json_decode($result);
			$hb_folders = $result_object->objects;
			if(!empty($hb_folders)) {
				foreach($hb_folders as $folder) {
					if(!array_key_exists($folder->id, $folderslist)) {
						$folder_data = array(
							'hb_folder_id' => $folder->id,
							'hb_folder_path' => $folder->full_path
						);

						if(file_exists(JPATH_SITE.$folder->full_path) && !in_array($folder->full_path,$folderslist)) {
							$folder_data['jpath'] = $folder->full_path;
							$folderslist[$folder->id] = $folder->full_path;
						}
						$this->save($folder_data);
					}
				}
			}
			$limitstart += $limit;
		} while($limitstart < $result_object->total_count);

		return true;
	}

	public function primefiles() {
		require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hubspotmigration'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'migration.php');

		$query = 'SELECT DISTINCT hb_folder_id, jpath FROM #__hubspotmigration_folders';
		$this->_db->setQuery($query);
		$folderslist = array();
		$pathslist = $this->_db->loadObjectList();
		foreach($pathslist as $path) {
			$folderslist[$path->hb_folder_id] = $path->jpath;
		}

		$query = 'SELECT DISTINCT hb_file_id FROM #__hubspotmigration_files';
		$this->_db->setQuery($query);
		$fileslist = $this->_db->loadColumn();

		$limit = 200;
		$limitstart = 0;
		do {
			$data = array(
				'limit' => $limit,
				'offset' => $limitstart,
			);
			$result = HubspotmigrationHelper::getRequest('/content/api/v2/files',$data);
			$http_code = HubspotmigrationHelper::getResponseCode();

			if($http_code != 200) return false;

			$result_object = json_decode($result);
			$hb_files = $result_object->objects;
			if(!empty($hb_files)) {
				foreach($hb_files as $file) {
					if(!in_array($file->id, $fileslist)) {
						$jpath = '';
						$filename = $file->name.'.'.$file->extension;
						if(!$file->folder_id) {
							$jpath = '/'.$filename;
						} elseif(array_key_exists($file->folder_id, $folderslist)) {
							$jpath = $folderslist[$file->folder_id].'/'.$filename;
						}
						if($jpath && file_exists(JPATH_SITE.$jpath)) {
							$file_data = array(
								'hb_file_id' => $file->id,
								'hb_file_path' => $file->url,
								'jpath' => $jpath
							);
							$this->save($file_data);
						}
					}
				}
			}
			$limitstart += $limit;
		} while($limitstart < $result_object->total_count);

		return true;
	}

	public function onBeforeSave(&$data, &$table) {
		if(!is_array($data)) $data = (array)$data;

		if($data['jpath'] && !file_exists(JPATH_SITE.$data['jpath'])) return false;
		if($data['hb_folder_id']) {
			require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hubspotmigration'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'migration.php');
			$result = HubspotmigrationHelper::getRequest('/content/api/v2/folders/'.$data['hb_folder_id']);
			$http_code = HubspotmigrationHelper::getResponseCode();
			if($http_code != 200) return false;
			$data['hb_folder_path'] = json_decode($result)->full_path;
		}

		return parent::onBeforeSave($data, $table);
	}
}
