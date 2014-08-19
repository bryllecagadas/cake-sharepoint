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
				secureId: '$secureId',
				data: $files
			};
		",
		array('inline' => false)
	);
	$this->Html->script('files', array('inline' => false));
?>
<div id='tree'></div>