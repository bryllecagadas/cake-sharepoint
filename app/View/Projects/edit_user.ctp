<h2><?php echo $project['Project']['name']; ?> Project <span class='label label-default'>Edit User</span></h2>
<div class="row">
  <div class="col-xs-3">
		<?php echo $this->Form->create('UserProjectRole', array(
			'type' => 'POST',
			'inputDefaults' => array(
				'div' => 'form-group',
				'wrapInput' => false,
				'class' => 'form-control'
			),
		)); ?>
		<fieldset>
			<legend>User: <?php echo $user_project_role['User']['username']; ?></legend>
			<?php echo $this->Form->input('user_id', array('label' => 'User', 'type' => 'hidden')); ?>
			<?php echo $this->Form->input('role_id', array('label' => 'Role')); ?>
			<?php echo $this->Form->submit('Save', array(
				'div' => 'form-group',
				'class' => 'btn btn-default'
			)); ?>
			<?php echo $this->Form->end(); ?>
		</fieldset>
	</div>
</div>