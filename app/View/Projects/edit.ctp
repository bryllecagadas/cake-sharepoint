<h2>
	<?php echo $project['Project']['name']; ?> Project 
	<span class='label label-default'>Edit</span>
</h2>
<?php echo $this->Form->create(null, array('type' => 'POST')); ?>
<?php echo $this->Form->input('name'); ?>
<?php echo $this->Form->end('Save'); ?>