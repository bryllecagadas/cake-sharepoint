<h2>Add User</h2>
<?php echo $this->Form->create(); ?>
<?php echo $this->Form->input('username'); ?>
<?php echo $this->Form->input('email'); ?>
<?php echo $this->Form->input('password', array('type' => 'password')); ?>
<?php echo $this->Form->input('password2', array('type' => 'password')); ?>
<?php echo $this->Form->input('admin', array('type' => 'checkbox', 'label' => 'Administrator')); ?>
<?php echo $this->Form->end('Save'); ?>