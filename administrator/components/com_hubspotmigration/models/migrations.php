<?php defined('_JEXEC') or die('Restricted Access');

class HubspotmigrationModelMigrations extends FOFModel {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function prepareMigration($migration_id) {
		$migration = FOFModel::getTmpInstance('migrations','hubspotmigrationModel')->getItem($migration_id);

		$query = 'SELECT k.id, k.publish_up, k.title
				  FROM #__k2_items k
				  LEFT JOIN #__hubspotmigration_blogs h ON k.id=h.k2_item_id
				  WHERE k.catid='.$migration->k2_category_id.'
				  AND k.published=1 AND k.trash=0 AND k.access=1
				  AND h.k2_item_id IS NULL';
		$this->_db->setQuery($query);
		$k2_items = $this->_db->loadObjectList();

		foreach($k2_items as $item) {
			$data = array(
				'blog_group_id' => $migration->blog_group_id,
				'k2_item_id' => $item->id,
				'title' => $item->title,
				'status' => 'new',
			);
			FOFModel::getTmpInstance('blogs','hubspotmigrationModel')->save($data);
		}
		return true;
	}

}
