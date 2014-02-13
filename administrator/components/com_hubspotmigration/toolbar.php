<?php
defined('_JEXEC') or die();

class HubspotmigrationToolbar extends FOFToolbar {
	public function onBrowse() {
		parent::onBrowse();
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_hubspotmigration');
	}

	public function onMigrationsBrowse() {
		parent::onBrowse();
		JToolBarHelper::divider();
		JToolBarHelper::custom('prepare','new','','Prepare');
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_hubspotmigration');
	}

	public function onBlogsBrowse() {
		parent::onBrowse();
		JToolBarHelper::divider();
		JToolBarHelper::custom('process','move','','Process',false);
		JToolBarHelper::custom('update','move','','Update',false);
		//JToolBarHelper::custom('test','move','','Test',false);
		JToolBarHelper::custom('mapurl','revert','','Create Redirect');
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_hubspotmigration');
	}

	public function onAuthorsBrowse() {
		parent::onBrowse();
		JToolBarHelper::divider();
		JToolBarHelper::custom('populate','export','','Populate',false);
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_hubspotmigration');
	}

	/* public function onFoldersBrowse() {
		parent::onBrowse();
		JToolBarHelper::divider();
		JToolBarHelper::custom('primefolders','export','','Prime Folders',false);
		JToolBarHelper::custom('primefiles','export','','Prime Files',false);
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_hubspotmigration');
	} */
}
