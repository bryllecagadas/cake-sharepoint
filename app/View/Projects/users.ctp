<h2>Users for <em><?php echo $project['Project']['name']; ?></em></h2>
<table>
	<thead>
		<tr>
			<th>ID</th>
			<th>Username</th>
			<th>Email</th>
			<th>Role</th>
			<th>Created</th>
			<th>Updated</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($users as $user) : ?>
		<tr>
			<td><?php echo $user['UserProjectRole']['id']; ?></td>
			<td><?php echo $user['User']['username']; ?></td>
			<td><?php echo $user['User']['email']; ?></td>
			<td><?php echo Inflector::humanize($user['Role']['name']); ?></td>
			<td><?php echo $user['UserProjectRole']['created']; ?></td>
			<td><?php echo $user['UserProjectRole']['updated']; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>