<?php
	$this->Html->script('jstree', array('inline' => false));
	$this->Html->css('/jstree-themes/default/style.min', array('inline' => false));
	$url = Router::url(array(
		'controller' => 'projects',
		'action' => 'files',
		'ajax' => true,
	));

	$permUrl = Router::url(array(
		'controller' => 'projects',
		'action' => 'node_permissions',
		'ajax' => true,
	));
	
	$this->Html->scriptBlock(
		"
			var Files = {
				url: '$url',
				permissionsUrl: '$permUrl',
				secureId: '$secureId'
			};
		",
		array('inline' => false)
	);
	$this->Html->script('files', array('inline' => false));
?>
<div id='tree'></div>
<?php if($user['admin']) : ?>
	<div class='tree-options'>
		<div class='role-switcher'>
			<h3>Filter files for:</h3>
			<?php foreach ($roles as $name => $role) : ?>
				<?php echo $this->Html->link(Inflector::humanize($name), $this->here, array('data-role' => $name, 'class' => 'role')); ?>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>