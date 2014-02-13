<?php defined('_JEXEC') or die('Restricted Access');

class HubspotmigrationControllerBlog extends FOFController {
	private $pause_time = 15;
	private $run_limit = -1;

	public function __construct($config = array()) {
		parent::__construct($config);

		$params = JComponentHelper::getParams('com_hubspotmigration');
		$this->pause_time = $params->get('pause_time',15);
		$this->run_limit = $params->get('run_limit',-1);

	}

	public function process() {
		$success = $this->_postblogs();
		if($success) {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=blogs','success');
		} else {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=blogs','one or more errors were encountered','error');
		}
	}

	private function _postblogs($id = null, &$has_run = 0) {
		if(is_null($id)) {
			$cid = $this->input->get('cid', array(), 'array');
			if(is_array($cid) && !empty($cid)) {
				foreach($cid as $id) {
					$id = (int) $id;
					if(!$id || !$this->_postblogs($id, $has_run)) return false;
				}
				return true;
			} else {
				$id = $this->input->get('id', false, 'int');
				if(!$id) {
					$db = JFactory::getDbo();
					$query = 'SELECT hubspotmigration_blog_id FROM #__hubspotmigration_blogs WHERE status="new"';
					$db->setQuery($query);
					$cid = $db->loadColumn();
					if(empty($cid)) return true;
					foreach($cid as $id) {
						if(!$this->_postblogs($id, $has_run)) return false;
					}
					return true;
				}
			}
		}
		if(!$id || !is_numeric($id)) return false;
		if($this->run_limit != -1 && $has_run >= $this->run_limit) return true;
		$has_run++;

		if(!$this->getThisModel()->postBlog($id)) return false;
		sleep($this->pause_time);
		return true;
	}

	public function update() {
		$success = $this->_updateblogs();
		if($success) {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=blogs','success');
		} else {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=blogs','one or more errors were encountered','error');
		}
	}

	private function _updateblogs() {
		$cid = $this->input->get('cid', array(), 'array');
		if(!is_array($cid) || empty($cid)) {
			$id = $this->input->get('id', false, 'int');
			if(!$id) return true;
			$cid = array($id);
		}
		$model = $this->getThisModel();
		foreach($cid as $id) {
			if(!$model->repostBlog($id)) return false;
			sleep($this->pause_time);
		}
		return true;
	}

	/* public function test() {
		$success = $this->_testupdate();
		if($success) {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=blogs','success');
		} else {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=blogs','one or more errors were encountered','error');
		}
	}

	private function _testupdate() {
		$model = $this->getThisModel();
		return $model->testRepost();
	} */

	public function mapurl() {
		$success = $this->_mapurls();
		if($success) {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=blogs','success');
		} else {
			$this->setRedirect('index.php?option=com_hubspotmigration&view=blogs','one or more errors were encountered','error');
		}
	}

	private function _mapurls($id = null) {
		if(is_null($id)) {
			$cid = $this->input->get('cid', array(), 'array');
			if(is_array($cid) && !empty($cid)) {
				foreach($cid as $id) {
					$id = (int) $id;
					if(!$id || !$this->_mapurls($id)) return false;
				}
				return true;
			} else {
				$id = $this->input->get('id', false, 'int');
			}
		}
		if(!$id || !is_numeric($id)) return false;

		if(!$this->getThisModel()->urlMap($id)) return false;
		sleep($this->pause_time);
		return true;
	}
}
