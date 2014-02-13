<?php defined('_JEXEC') or die('Restricted access');
$params = JComponentHelper::getParams('com_hubspotmigration');
$hubspot_url = $params->get('hubspot_url','');
if($hubspot_url) $hubspot_url = rtrim($hubspot_url,'/');
$site_url = rtrim(JURI::root(),'/');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th><input type="checkbox" name="checkall-toggle" value="" title="<?= JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
				<th><?php echo JHTML::_('grid.sort', 'ID', 'ID', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'Title', 'title', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'Status', 'status', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'K2 Link', 'k2_link', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'Hubspot Link', 'hb_blog_link', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'Hubspot ID', 'hb_blog_id', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th>Details</th>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td><input type="text" name="title" value="<?=$this->input->get('title','')?>" /></td>
				<td><?=JHtml::_('select.genericlist', array(''=>'--select a status--','new'=>'new','created'=>'created','errored'=>'errored'), 'status', array('onchange'=>'Joomla.submitform();'), '', '', $this->input->get('status',''))?></td>
				<td></td>
				<td><?=JHtml::_('select.genericlist', array(''=>'--url state--','same'=>'same','mapped'=>'mapped','unmapped'=>'unmapped'), 'urlmapped', array('onchange'=>'Joomla.submitform();'), '', '', $this->input->get('urlmapped',''))?></td>
				<td></td>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="20">
					<?php if($this->pagination->total > 0) echo $this->pagination->getListFooter() ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php if(!empty($this->items)):
				$m = 1;
				foreach($this->items as $i => $item):
					$m = 1-$m;
					$linkclass = ($item->hb_blog_link)?(($item->k2_link == $item->hb_blog_link || $item->link_mapped)?'links_ok':'links_bad'):'links_unknown';
					$hubspot_link = $item->hb_blog_link;
					if($hubspot_url && $item->hb_blog_link) $hubspot_link = "<a href=\"$hubspot_url$hubspot_link\">$hubspot_link</a>";
					$site_link = $item->k2_link;
					if($site_link && $site_url) $site_link = "<a href=\"$site_url$site_link\">$site_link</a>";
					?>
					<tr class="row<?=$m ?><?=($item->status=='errored')?' has_error':''?>">
						<td align="center"><?=JHtml::_('grid.id',$i,$item->hubspotmigration_blog_id);?></td>
						<td><?=$item->hubspotmigration_blog_id?></td>
						<td><a href="index.php?option=com_k2&view=item&cid=<?=$item->k2_item_id?>"><?=$item->title?></a></td>
						<td><?=$item->status?></td>
						<td class="<?=$linkclass?>"><?=$site_link?></td>
						<td class="<?=$linkclass?>"><?=$hubspot_link?></td>
						<td><?=$item->hb_blog_id?></td>
						<td><?=$item->details?></td>
					</tr>
				<?php endforeach;
			else: ?>
				<tr>
					<td colspan="20">No blog posts yet</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<input type="hidden" name="option" id="option" value="com_hubspotmigration" />
	<input type="hidden" name="view" id="view" value="blogs" />
	<input type="hidden" name="task" id="task" value="browse" />
	<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<style type="text/css">
	.links_ok, .links_ok a{color:#090;}
	.links_bad, .links_bad a{color:#F44;}
	table.adminlist tbody tr.has_error td{background-color:#FCC;}
</style>
