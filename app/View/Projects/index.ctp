<h2>Projects</h2>
<div class='action-buttons'>
	<?php 
		echo $this->Html->link(
			'<span class="glyphicon glyphicon-plus"></span> Add Project', 
			array(
				'controller' => 'projects',
				'action' => 'add',
			), 
			array('class' => 'btn btn-primary', 'escapeTitle' => false)
		); 
	?>
	<span class="badge pull-right"><?php echo count($projects); ?></span>
</div>
<div class="table-responsive">
	<table class='table table-hover'>
		<thead>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Added</th>
				<th>Modified</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($projects as $project) : ?>
			<tr>
				<td><?php echo $project['Project']['id']; ?></td>
				<td><?php echo $this->Html->link($project['Project']['name'], array('action' => 'files', $project['Project']['id'])); ?></td>
				<td><?php echo $project['Project']['created']; ?></td>
				<td><?php echo $project['Project']['updated']; ?></td>
				<td>
					<?php echo $this->Html->link('Users', array('action' => 'users', $project['Project']['id'])); ?> | 
					<?php echo $this->Html->link('Edit', array('action' => 'edit', $project['Project']['id'])); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>