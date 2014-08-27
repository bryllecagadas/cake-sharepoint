<h2><?php echo $project['Project']['name']; ?> Project | <small>Users</small></h2>
<div class="table-responsive">
	<table class='table table-hover'>
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
</div>