<h2>Projects</h2>
<?php if ($add_project) : ?>
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
<?php endif; ?>
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
		<?php if($projects) : ?>
			<?php foreach($projects as $project) : ?>
				<tr>
					<td><?php echo $project['Project']['id']; ?></td>
					<td><?php echo $this->Html->link($project['Project']['name'], array('action' => 'files', $project['Project']['id'])); ?></td>
					<td><?php echo $project['Project']['created']; ?></td>
					<td><?php echo $project['Project']['updated']; ?></td>
					<td>
						<?php echo $project['users_action'] ? $this->Html->link('Users', array('action' => 'users', $project['Project']['id'])) : ''; ?> 
						<?php echo $project['edit_action'] ? ($project['users_action'] ? ' | ' : '') . $this->Html->link('Edit', array('action' => 'edit', $project['Project']['id'])) : ''; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan='6'>You don't belong to any projects yet.</td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>
</div>