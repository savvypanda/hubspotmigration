<?php defined('_JEXEC') or die('Restricted Access');

class HubspotmigrationControllerAuthor extends FOFController {
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function populate() {
		$success = $this->_populateAuthors();
		if($success) {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=authors','success');
		} else {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=authors','one or more errors were encountered','error');
		}
	}

	private function _populateAuthors() {
		return $this->getThisModel()->populateHubspotAuthors();
	}
}