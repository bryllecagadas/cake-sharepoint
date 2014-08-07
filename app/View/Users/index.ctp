<h2>Users</h2>
<table>
	<thead>
		<tr>
			<th>ID</th>
			<th>Username</th>
			<th>Email</th>
			<th>Administrator</th>
			<th>Created</th>
			<th>Updated</th>
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
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>