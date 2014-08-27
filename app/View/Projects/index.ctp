<h2>Projects</h2>
<div class="table-responsive">
	<table class='table table-hover'>
		<thead>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Created</th>
				<th>Updated</th>
				<th>List</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($projects as $project) : ?>
			<tr>
				<td><?php echo $project['Project']['id']; ?></td>
				<td><?php echo $project['Project']['name']; ?></td>
				<td><?php echo $project['Project']['created']; ?></td>
				<td><?php echo $project['Project']['updated']; ?></td>
				<td>
					<?php echo $this->Html->link('Users', array('action' => 'users', $project['Project']['id'])); ?> | 
					<?php echo $this->Html->link('Files', array('action' => 'files', $project['Project']['id'])); ?>
				</td>
				<td>
					<?php echo $this->Html->link('Add User', array('action' => 'add_user', $project['Project']['id'])); ?> | 
					<?php echo $this->Html->link('Edit', array('action' => 'edit', $project['Project']['id'])); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>