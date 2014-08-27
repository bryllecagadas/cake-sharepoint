<h2><?php echo $user['User']['username']; ?> <span class='label label-default'>Edit</span></h2>
<?php echo $this->Form->create(null, array('type' => 'POST')); ?>
<?php echo $this->Form->input('username'); ?>
<?php echo $this->Form->input('email'); ?>
<?php echo $this->Form->input('password', array('type' => 'password')); ?>
<?php echo $this->Form->input('password2', array('type' => 'password')); ?>
<?php echo $this->Form->input('admin', array('type' => 'checkbox', 'label' => 'Administrator')); ?>
<?php echo $this->Form->end('Save'); ?>