<h2>Add Project</h2>
<div class="row">
  <div class="col-xs-3">
		<?php echo $this->Form->create(null, array(
			'inputDefaults' => array(
				'div' => 'form-group',
				'wrapInput' => false,
				'class' => 'form-control'
			),
		)); ?>
		<?php echo $this->Form->input('name'); ?>
		<?php echo $this->Form->submit('Save', array(
			'div' => 'form-group',
			'class' => 'btn btn-default'
		)); ?>
		<?php echo $this->Form->end(); ?>
	</div>
</div>