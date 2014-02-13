<?php defined('_JEXEC') or die('Restricted Access');

class HubspotmigrationControllerFolder extends FOFController {

	public function __construct($config = array()) {
		return parent::__construct($config);
	}

	public function prime() {
		$success = $this->_primefolders();
		if($success) {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=folders','success');
		} else {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=folders','one or more errors were encountered');
		}
	}

	private function _primefolders() {
		return $this->getThisModel()->prime();
	}
}