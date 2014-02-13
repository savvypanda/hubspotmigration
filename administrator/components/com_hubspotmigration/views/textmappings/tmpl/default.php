<?php defined('_JEXEC') or die('Restricted access');
//JHtml::_('sortablelist.sortable', 'adminTable', 'adminForm', $this->lists->order_Dir, 'index.php?option=com_hubspotmigration&view=textmappings&task=saveorder&format=raw');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th style="width:120px">
					<?= JHtml::_('grid.sort', 'Ordering', 'ordering', $this->lists->order_Dir, $this->lists->order); ?>
					<?php echo JHtml::_('grid.order',  $this->items); ?>
				</th>
				<th style="width:20px;"><input type="checkbox" name="checkall-toggle" value="" title="<?= JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
				<th><?php echo JHTML::_('grid.sort', 'Map From', 'regex_from', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'Map To', 'regex_to', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th style="width:80px;"><?php echo JHTML::_('grid.sort', 'Enabled', 'enabled', $this->lists->order_Dir, $this->lists->order) ?></th>
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
					$m = 1-$m; ?>
					<tr class="row<?=$m ?>">
						<td align="right">
							<?php echo $this->pagination->orderUpIcon($i); ?>
							<?php echo $this->pagination->orderDownIcon($i, $this->pagination->total+2); ?>
							<input type="text" name="order[]" size="5" value="<?=$item->ordering;?>" class="text-area-order" />
						</td>
						<td align="center"><?=JHtml::_('grid.id',$i,$item->hubspotmigration_textmapping_id);?></td>
						<td><?=htmlentities($item->regex_from)?></td>
						<td><?=htmlentities($item->regex_to)?></td>
						<td align="center"><?= JHtml::_('grid.published', $item->enabled, $i); ?></td>
					</tr>
				<?php endforeach;
			else: ?>
				<tr>
					<td colspan="20">No mappings yet</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<input type="hidden" name="option" id="option" value="com_hubspotmigration" />
	<input type="hidden" name="view" id="view" value="textmappings" />
	<input type="hidden" name="task" id="task" value="browse" />
	<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>