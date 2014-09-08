<?php
	$this->Html->script('jstree', array('inline' => false));
	$this->Html->script('jstreegrid', array('inline' => false));
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

	$session_id = $upload_url = '';
	
	if ($has_permission) {
		// We use jQuery file upload now
		$this->Html->css('/jquery-file-upload/css/jquery.fileupload', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/vendor/jquery.ui.widget', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/vendor/tmpl.min', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/vendor/load-image.min', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/vendor/canvas-to-blob.min', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/vendor/jquery.blueimp-gallery.min', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/jquery.iframe-transport', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/jquery.fileupload', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/jquery.fileupload-process', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/jquery.fileupload-image', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/jquery.fileupload-audio', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/jquery.fileupload-video', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/jquery.fileupload-validate', array('inline' => false));
		$this->Html->script('/jquery-file-upload/js/jquery.fileupload-ui', array('inline' => false));

		$session_id = CakeSession::id();
		$upload_url = Router::url(array(
			'controller' => 'projects',
			'action' => 'file_upload',
			'ajax' => true,
		));
	}

	$download_url = Router::url(array(
		'controller' => 'projects',
		'action' => 'download',
	), TRUE);

	$this->Html->scriptBlock(
		"
			var Files = {
				acoAlias: '$aco_alias',
				downloadUrl: '$download_url',
				url: '$url',
				permissionsUrl: '$permUrl',
				secureId: '$secureId',
				sessionId: '$session_id',
				uploadUrl: '$upload_url'
			};
		",
		array('inline' => false)
	);

	$this->Html->script('files', array('inline' => false));
?>
<h2><?php echo $project['Project']['name']; ?> Project <span class='label label-default'>Files</span></h2>
<?php if ($has_permission) : ?>
	<ul class="nav nav-tabs role-switcher" role="tablist">
		<?php if ($auth_user['admin']) : ?>
	  	<li class="active"><a href="#" class='role' data-role='admin'>Admin</a></li>
	  <?php endif; ?>
	  <?php foreach ($roles as $name => $role) : ?>
			<li class="<?php echo !$auth_user['admin'] && $name == 'project_manager' ? 'active' : ''; ?>">
				<?php 
					echo $this->Html->link(
						$role['Role']['title'], 
						$this->here, 
						array(
							'data-role' => $name, 
							'class' => 'role', 
							'data-checkbox' => ($name == 'project_manager' && in_array($name, $user_roles) ? '0' : '1')
						)
					); 
				?>
			</li>
		<?php endforeach; ?>
	</ul>
	<div class='save-tree-options-wrapper'>
		<?php echo $this->Html->link('Save', $this->here, array('class' => 'save-tree-options btn btn-primary')); ?>
	</div>
<?php endif; ?>
<div class='filetree-container'>
	<div id='tree'></div>
</div>
<?php if ($has_permission) : ?>
	<div class='file-upload-wrapper'>
		<h3>
			Upload files
			<span class='label label-default destination'></span>
		</h3>
		<?php echo $this->Form->create(null, array('url' => $upload_url, 'id' => 'fileupload')); ?>
			<noscript><input type="hidden" name="redirect" value="http://blueimp.github.io/jQuery-File-Upload/"></noscript>
				<div class="row fileupload-buttonbar">
					<div class="col-lg-7">
						<span class="btn btn-success fileinput-button">
							<i class="glyphicon glyphicon-plus"></i>
							<span>Add files...</span>
							<?php echo $this->Form->file('file', array('name' => 'files[]', 'label' => false, 'multiple' => true)); ?>
						</span>
						<button type="submit" class="btn btn-primary start">
							<i class="glyphicon glyphicon-upload"></i>
							<span>Start upload</span>
						</button>
						<button type="reset" class="btn btn-warning cancel">
							<i class="glyphicon glyphicon-ban-circle"></i>
							<span>Cancel upload</span>
						</button>
						<button type="button" class="btn btn-danger delete">
							<i class="glyphicon glyphicon-trash"></i>
							<span>Delete</span>
						</button>
						<input type="checkbox" class="toggle">
						<span class="fileupload-process"></span>
					</div>
					<div class="col-lg-5 fileupload-progress fade">
						<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
							<div class="progress-bar progress-bar-success" style="width:0%;"></div>
						</div>
						<div class="progress-extended">&nbsp;</div>
					</div>
				</div>
				<table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
		<?php echo $this->Form->end(); ?>
	</div>
<?php endif; ?>
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-primary start" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td>
            {% if (file.deleteUrl) { %}
                <button class="btn btn-danger delete" data-data="{%=file.data%}" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>Delete</span>
                </button>
                <input type="checkbox" name="delete" value="1" class="toggle">
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>