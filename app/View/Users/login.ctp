<h2>Login</h2>
<?php echo $this->Form->create(); ?>
<?php echo $this->Session->flash('auth'); ?>
<?php echo $this->Form->input('username'); ?>
<?php echo $this->Form->input('password', array('type' => 'password')); ?>
<?php echo $this->Form->end('Login'); ?>