<fieldset>
	<legend>Login</legend>
	<?php echo $this->Form->create(null, array(
		'type' => 'POST',
		'inputDefaults' => array(
			'div' => 'form-group',
			'wrapInput' => false,
			'class' => 'form-control'
		),
	)); ?>
	<div class="row">
	  <div class="col-xs-4">
			<?php echo $this->Session->flash('auth'); ?>
		</div>
	</div>
	<div class="row">
	  <div class="col-xs-3">
				<?php echo $this->Form->input('username'); ?>
				<?php echo $this->Form->input('password', array('type' => 'password')); ?>
				<?php echo $this->Form->submit('Login', array(
					'div' => 'form-group',
					'class' => 'btn btn-default'
				)); ?>
				<?php echo $this->Form->end(); ?>
		</div>
	</div>
</fieldset>