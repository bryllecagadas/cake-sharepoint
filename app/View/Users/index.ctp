<h2>Users</h2>
<div class='action-buttons'>
	<?php 
		echo $this->Html->link(
			'<span class="glyphicon glyphicon-plus"></span> Add User', 
			array(
				'controller' => 'users',
				'action' => 'add',
			), 
			array('class' => 'btn btn-primary', 'escapeTitle' => false)
		); 
	?>
	<span class="badge pull-right"><?php echo count($users); ?></span>
</div>
<div class="table-responsive">
	<table class='table table-hover'>
		<thead>
			<tr>
				<th>ID</th>
				<th>Username</th>
				<th>Email</th>
				<th>Administrator</th>
				<th>Added</th>
				<th>Modified</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($users as $user) : ?>
			<tr>
				<td><?php echo $user['User']['id']; ?></td>
				<td><?php echo $user['User']['username']; ?></td>
				<td><?php echo $user['User']['email']; ?></td>
				<td><?php echo $user['User']['admin'] ? $this->Html->tag('span', null, array('class' => 'glyphicon glyphicon-ok')) : ''; ?></td>
				<td><?php echo $user['User']['created']; ?></td>
				<td><?php echo $user['User']['updated']; ?></td>
				<td><?php echo $this->Html->link('Edit', array('action' => 'edit', $user['User']['id'])); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>