<h2>Activity Logs</h2>
<span class='small'>Click Variables and Location to view the full value</span>
<div class="row">
	<?php
		echo $this->Form->create(false, array(
			'url' => array('action' => 'process'),
			'class' => array('form-inline'),
			'inputDefaults' => array(
				'div' => 'form-group',
				'wrapInput' => false,
				'class' => 'form-control'
			),
		));
	?>
	<div class='col-xs-5 col-md-offset-7'>
		<span class='pull-right'>
			<?php echo $this->Form->input('process', array('options' => $options, 'type' => 'select', 'label' => false)); ?>
			<?php echo $this->Form->submit('Proceed', array('div' => 'form-group', 'class' => 'btn btn-primary')); ?>
		</span>
	</div>
	<?php echo $this->Form->end(); ?>
</div>
<div class="table-responsive">
	<table class='table table-hover'>
		<thead>
			<tr>
				<th>ID</th>
				<th>Message</th>
				<th>Details</th>
				<th>Timestamp</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($logs as $log) : ?>
			<tr>
				<td><?php echo $log['Log']['id']; ?></td>
				<td><?php echo $this->String->format($log['Log']['message'], $log['replacement'] + unserialize($log['Log']['variables'])); ?></td>
				<td>
					<a class='hover' href='#'>+ Details</a><br />
					<pre class='hover-item'>
				    <?php echo print_r(unserialize($log['Log']['variables']) + array('location' => $log['Log']['location'], 'referer' => $log['Log']['referer']), 1); ?>
					</pre>
				</td>
				<td><?php echo $log['Log']['created']; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
<div class='paginator'>
	<ul class='pagination'>
		<?php if ($this->Paginator->hasPage()): ?>
			<?php echo $this->Paginator->first( __('First'), array('tag' => 'li')); ?>
			<?php echo $this->Paginator->prev( __('Prev'), array('tag' => 'li'), null, array('class' => 'prev disabled',)); ?>
			<?php echo $this->Paginator->numbers(array('separator' => '', 'tag' => 'li', 'currentClass' => 'active', 'currentTag' => 'a')); ?>
			<?php echo $this->Paginator->next( __('Next'), array('tag' => 'li'), null, array('class' => 'next disabled')); ?>
			<?php echo $this->Paginator->last( __('Last'), array('tag' => 'li')); ?>
		<?php endif; ?>
	</ul>
</div>
<?php
$script = <<<JS
(function($) {
	$(document).ready(function() {
		$('.hover').click(function() {
			if (!$(this).hasClass('display')) {
				$(this).addClass('display');
				$(this).siblings('.hover-item').show();
				$(this).html($(this).html().replace('+', '-'));
			} else {
				$(this).removeClass('display');
				$(this).siblings('.hover-item').hide();
				$(this).html($(this).html().replace('-', '+'));
			}
			return false;
		});
	});
})(jQuery);
JS;

$this->Html->scriptBlock($script, array('inline' => false));
?>