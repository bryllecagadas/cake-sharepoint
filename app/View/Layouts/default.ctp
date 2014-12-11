<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		Sharepoint | <?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->css('cake.generic');
		echo $this->Html->script('jquery');	
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
	<base href='<?php echo Router::url('/', true); ?>' />
</head>
<body>
	<div id="container">
		<div id="header">
			<h1>Sharepoint</h1>
		</div>
		<div id="content">
			<?php echo $this->Session->flash(); ?>
			<?php echo $this->fetch('content'); ?>
		</div>
		<div id="footer">&copy; Gemango</div>
	</div>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
