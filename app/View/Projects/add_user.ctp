<h2><?php echo $project['Project']['name']; ?> Project <span class='label label-default'>Add User</span></h2>
<div class="row">
  <div class="col-xs-3">
		<?php echo $this->Form->create('UserProjectRole', array(
			'inputDefaults' => array(
				'div' => 'form-group',
				'wrapInput' => false,
				'class' => 'form-control'
			),
		)); ?>
		<?php echo $this->Form->input('user_id', array('label' => 'User')); ?>
		<?php echo $this->Form->input('role_id', array('label' => 'Role')); ?>
		<?php echo $this->Form->submit('Add User', array(
			'div' => 'form-group',
			'class' => 'btn btn-default'
		)); ?>
		<?php echo $this->Form->end(); ?>
	</div>
</div>