<h2>Activity Logs</h2>
<span class='small'>Click Variables and Location to view the full value</span>
<div class="table-responsive">
	<table class='table table-hover'>
		<thead>
			<tr>
				<th>ID</th>
				<th>User</th>
				<th>Message</th>
				<th>Variables</th>
				<th>Location</th>
				<th>Referer</th>
				<th>Added</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($logs as $log) : ?>
			<tr>
				<td><?php echo $log['Log']['id']; ?></td>
				<td><?php echo $log['User']['username']; ?></td>
				<td><?php echo $log['Log']['message']; ?></td>
				<td><a class='hover' href='#'>+ Variables</a><br /><pre class='hover-item'><?php echo print_r(unserialize($log['Log']['variables']), 1); ?></pre></td>
				<td><a class='hover' href='#'>+ Location</a><br /><span class='hover-item'><?php echo $log['Log']['location']; ?></span></td>
				<td><?php echo $log['Log']['referer']; ?></td>
				<td><?php echo $log['Log']['created']; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
<div class='paginator'>
	<ul class='pagination'>
		<?php echo $this->Paginator->first( __('First'), array('tag' => 'li')); ?>
		<?php echo $this->Paginator->prev( __('Prev'), array('tag' => 'li'), null, array('class' => 'prev disabled',)); ?>
		<?php echo $this->Paginator->numbers(array('separator' => '', 'tag' => 'li', 'currentClass' => 'active', 'currentTag' => 'a')); ?>
		<?php echo $this->Paginator->next( __('Next'), array('tag' => 'li'), null, array('class' => 'next disabled')); ?>
		<?php echo $this->Paginator->last( __('Last'), array('tag' => 'li')); ?>
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