<h2>Users</h2>
<div class="table-responsive">
	<table class='table table-hover'>
		<thead>
			<tr>
				<th>ID</th>
				<th>Username</th>
				<th>Email</th>
				<th>Administrator</th>
				<th>Created</th>
				<th>Updated</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($users as $user) : ?>
			<tr>
				<td><?php echo $user['User']['id']; ?></td>
				<td><?php echo $user['User']['username']; ?></td>
				<td><?php echo $user['User']['email']; ?></td>
				<td><?php echo $user['User']['admin']; ?></td>
				<td><?php echo $user['User']['created']; ?></td>
				<td><?php echo $user['User']['updated']; ?></td>
				<td><?php echo $this->Html->link('Edit', array('action' => 'edit', $user['User']['id'])); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>