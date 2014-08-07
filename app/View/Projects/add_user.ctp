<h2>Add user for <em><?php echo $project['Project']['name']; ?></em></h2>
<?php echo $this->Form->create('UserProjectRole'); ?>
<?php echo $this->Form->input('user_id', array('label' => 'User')); ?>
<?php echo $this->Form->input('role_id', array('label' => 'Role')); ?>
<?php echo $this->Form->end('Add User'); ?>