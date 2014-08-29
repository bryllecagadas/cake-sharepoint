<h2><?php echo $user['User']['username']; ?> <span class='label label-default'>Edit</span></h2>
<div class="row">
  <div class="col-xs-3">
		<?php echo $this->Form->create(null, array(
			'type' => 'POST',
			'inputDefaults' => array(
				'div' => 'form-group',
				'wrapInput' => false,
				'class' => 'form-control'
			),
		)); ?>
		<?php echo $this->Form->input('username'); ?>
		<?php echo $this->Form->input('email'); ?>
		<?php echo $this->Form->input('password', array('type' => 'password')); ?>
		<?php echo $this->Form->input('password2', array('type' => 'password')); ?>
		<?php echo $this->Form->input('admin', array('type' => 'checkbox', 'label' => 'Administrator', 'class' => false)); ?>
		<?php echo $this->Form->submit('Save', array(
			'div' => 'form-group',
			'class' => 'btn btn-default'
		)); ?>
		<?php echo $this->Form->end(); ?>
	</div>
</div>