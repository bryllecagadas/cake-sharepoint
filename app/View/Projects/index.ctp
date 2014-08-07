<h2>Projects</h2>
<table>
	<thead>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Created</th>
			<th>Updated</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($projects as $project) : ?>
		<tr>
			<td><?php echo $project['Project']['id']; ?></td>
			<td><?php echo $project['Project']['name']; ?></td>
			<td><?php echo $project['Project']['created']; ?></td>
			<td><?php echo $project['Project']['updated']; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>