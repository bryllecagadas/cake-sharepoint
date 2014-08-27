<h2><?php echo $project['Project']['name']; ?> Project <span class='label label-default'>Add User</span></h2>
<?php echo $this->Form->create('UserProjectRole'); ?>
<?php echo $this->Form->input('user_id', array('label' => 'User')); ?>
<?php echo $this->Form->input('role_id', array('label' => 'Role')); ?>
<?php echo $this->Form->end('Add User'); ?>