<h2>
	<?php echo $project['Project']['name']; ?> Project 
	<span class='label label-default'>Users</span>
</h2>
<?php if ($add_user) : ?>
	<div class='action-buttons'>
		<?php 
			echo $this->Html->link(
				'<span class="glyphicon glyphicon-plus"></span> Add User', 
				array(
					'controller' => 'projects',
					'action' => 'add_user',
					$project['Project']['id']
				), 
				array('class' => 'btn btn-primary', 'escapeTitle' => false)
			); 
		?>
		<span class="badge pull-right"><?php echo count($users); ?></span>
	</div>
<?php endif; ?>
<div class="table-responsive">
	<table class='table table-hover'>
		<thead>
			<tr>
				<th>ID</th>
				<th>Username</th>
				<th>Email</th>
				<th>Role</th>
				<th>Added</th>
				<th>Modified</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php if($users) : ?>
			<?php foreach($users as $user) : ?>
				<tr>
					<td><?php echo $user['UserProjectRole']['id']; ?></td>
					<td><?php echo $user['User']['username']; ?></td>
					<td><?php echo $user['User']['email']; ?></td>
					<td><?php echo $user['Role']['title']; ?></td>
					<td><?php echo $user['UserProjectRole']['created']; ?></td>
					<td><?php echo $user['UserProjectRole']['updated']; ?></td>
					<td>
						<?php if ($user['edit_user']) : ?>
							<?php echo $this->Html->link('Edit', array(
								'action' => 'edit_user',
								$user['UserProjectRole']['project_id'],
								$user['UserProjectRole']['user_id']
							)); ?>
						<?php endif; ?>
						<?php if ($user['remove_user']) : ?>
							<?php if($user['edit_user']) : ?>
							 | 
							<?php endif; ?>
							<?php echo $this->Html->link('Remove', array(
								'action' => 'remove_user',
								$user['UserProjectRole']['project_id'],
								$user['UserProjectRole']['user_id']
							)); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan='6'>No users were added to the project</td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>
</div>