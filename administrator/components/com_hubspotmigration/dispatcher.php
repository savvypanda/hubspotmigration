<?php
defined('_JEXEC') or die('Restricted Access');

class HubspotmigrationDispatcher extends FOFDispatcher {
	public $defaultView = 'migrations';

	public function dispatch() {
		return parent::dispatch();
	}
}
