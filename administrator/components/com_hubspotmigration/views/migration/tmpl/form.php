<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<h1><?=($this->item->hubspotmigration_migration_id)?'Edit':'New'?> Migration</h1>
	<label for="k2_category_id">K2 Category ID: </label><input type="text" name="k2_category_id" value="<?=$this->item->k2_category_id?>" />
	<label for="blog_group_id">Blog Group ID: </label><input type="text" name="blog_group_id" value="<?=$this->item->blog_group_id?>" />

	<input type="hidden" name="hubspotmigration_migration_id" value="<?=$this->item->hubspotmigration_migration_id?>" />
	<input type="hidden" name="option" id="option" value="com_hubspotmigration" />
	<input type="hidden" name="view" id="view" value="migration" />
	<input type="hidden" name="task" id="task" value="edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>