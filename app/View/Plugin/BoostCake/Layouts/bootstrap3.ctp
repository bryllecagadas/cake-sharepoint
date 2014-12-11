<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>
		Sharepoint | <?php echo $title_for_layout; ?>
	</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Le styles -->
	<style>
	body {
		padding-top: 70px; /* 70px to make the container go all the way to the bottom of the topbar */
	}
	.affix {
		position: fixed;
		top: 60px;
		width: 220px;
	}
	</style>

	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<?php
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->Html->css('bootstrap.min');
		echo $this->Html->css('styles');
	?>
	<base href='<?php echo Router::url('/', true); ?>' />
</head>

<body>
	<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<?php echo $this->Html->link('Sharepoint', array(
					'controller' => 'pages',
					'action' => 'home'
				), array('class' => 'navbar-brand')); ?>
			</div>
			<div class="collapse navbar-collapse navbar-ex1-collapse">
				<?php if($auth_user) : ?>
					<ul class="nav navbar-nav">
						<?php if ($menu['projects']) : ?>
							<li class='<?php echo $this->params['controller'] == 'projects' ? 'active' : ''; ?>'>
								<?php echo $this->Html->link('Projects', array(
									'controller' => 'projects',
									'action' => 'index'
								)); ?>
							</li>
						<?php endif; ?>
						<?php if ($menu['users']) : ?>
							<li class='<?php echo $this->params['controller'] == 'users' && empty($current_user) ? 'active' : ''; ?>'>
								<?php echo $this->Html->link('Users', array(
									'controller' => 'users',
									'action' => 'index'
								)); ?>
							</li>
						<?php endif; ?>
						<?php if ($menu['logs']) : ?>
							<li class='<?php echo $this->params['controller'] == 'logs' ? 'active' : ''; ?>'>
								<?php echo $this->Html->link('Logs', array(
									'controller' => 'logs',
									'action' => 'index'
								)); ?>
							</li>
						<?php endif; ?>
					</ul>
				<?php endif; ?>
				
				<ul class="nav navbar-nav navbar-right">
					<?php if($auth_user) : ?>
						<li class='<?php echo !empty($current_user) ? 'active' : ''; ?> '><?php echo $this->Html->link($auth_user['username'], "/users/edit/" . $auth_user['id']); ?></li>
	        	<li><?php echo $this->Html->link('Logout', "/users/logout"); ?></li>
	        <?php else : ?>
	        	<li class='<?php echo $this->params['controller'] == 'users' && $this->params['action'] == 'login' ? 'active' : ''; ?>'>
	        		<?php echo $this->Html->link('Login', "/users/login"); ?>
	        	</li>
	       	<?php endif; ?>
	      </ul>
			</div>
		</div>
	</nav>

	<div class="container">
		<?php echo $this->Session->flash(); ?>
		<?php echo $this->fetch('content'); ?>
	</div><!-- /container -->

	<!-- Le javascript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<?php echo $this->Html->script('jquery'); ?>
	<?php echo $this->Html->script('bootstrap.min'); ?>
	<?php echo $this->fetch('script'); ?>
</body>
</html>