<?php defined('_JEXEC') or die('Restricted Access');

class HubspotmigrationControllerMigration extends FOFController {

	public function __construct($config = array()) {
		return parent::__construct($config);
	}

	public function prepare() {
		$success = $this->_prepareblogs();
		if($success) {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=blogs','success');
		} else {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=migrations','one or more errors were encountered');
		}
	}

	private function _prepareblogs($id = null) {
		if(is_null($id)) {
			$cid = $this->input->get('cid', array(), 'array');
			if(is_array($cid) && !empty($cid)) {
				foreach($cid as $id) {
					$id = (int) $id;
					if(!$id || !$this->_prepareblogs($id)) return false;
				}
				return true;
			} else {
				$id = $this->input->get('id', false, 'int');
			}
		}
		if(!$id || !is_numeric($id)) return false;

		return $this->getThisModel()->prepareMigration($id);
	}
}