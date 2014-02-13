<?php defined('_JEXEC') or die('Restricted access');
$itemstatus = $this->item->status?$this->item->status:'new';
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<h1><?=($this->item->hubspotmigration_blog_id)?'Edit':'New'?> Blog Mapping</h1>
	<p><label for="k2_item_id">K2 Item ID: </label><input type="text" name="k2_item_id" value="<?=$this->item->k2_item_id?>" /> <?=$this->item->title?></p>
	<p><label for="hb_blog_id">Hubspot Blog ID: </label><input type="text" name="hb_blog_id" value="<?=$this->item->hb_blog_id?>" /> <?=$this->item->hb_blog_link?></p>
	<p><label for="blog_group_id">Blog Group ID: </label><input type="text" name="blog_group_id" value="<?=$this->item->blog_group_id?>" /></p>
	<p style="margin:0;font-size:small;font-style:italic;">Status: <?=$itemstatus?></p>

	<input type="hidden" name="hubspotmigration_blog_id" value="<?=$this->item->hubspotmigration_blog_id?>" />
	<input type="hidden" name="option" id="option" value="com_hubspotmigration" />
	<input type="hidden" name="edited" value="1" />
	<input type="hidden" name="status" value="<?=$itemstatus?>" />
	<input type="hidden" name="view" id="view" value="blog" />
	<input type="hidden" name="task" id="task" value="edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>
