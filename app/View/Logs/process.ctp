<h2><?php echo $message; ?></h2>
<?php 
	echo $this->Form->create(false, array(
		'url' => array('action' => 'process'),
		'class' => array('form-inline'),
		'inputDefaults' => array(
			'div' => 'form-group',
			'wrapInput' => false,
			'class' => 'form-control'
		),
	)); 
?>
<?php echo $this->Form->input('proceed', array('type' => 'hidden', 'value' => 1)); ?>
<?php echo $this->Form->input('process', array('type' => 'hidden', 'value' => $process)); ?>
<?php echo $this->Form->submit('Continue', array('div' => 'form-group', 'class' => 'btn btn-primary')); ?>
<?php echo $this->Form->end(); ?>