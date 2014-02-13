<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<h1><?=($this->item->hubspotmigration_author_id)?'Edit':'New'?> Author Mapping</h1>
	<p><label for="juser_id">Joomla User ID: </label><input type="text" name="juser_id" value="<?=$this->item->juser_id?>" /> <?=$this->item->juser_name?></p>
	<p><label for="hb_author_id">Hubspot Author ID: </label><input type="text" name="hb_author_id" value="<?=$this->item->hb_author_id?>" /> <?=$this->item->hb_author_name?></p>
	<p style="margin:0;font-size:small;font-style:italic;">Created <?=($this->item->auto_created)?'Automatically':'Manually'?></p>

	<input type="hidden" name="hubspotmigration_author_id" value="<?=$this->item->hubspotmigration_author_id?>" />
	<input type="hidden" name="option" id="option" value="com_hubspotmigration" />
	<input type="hidden" name="view" id="view" value="author" />
	<input type="hidden" name="task" id="task" value="edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>