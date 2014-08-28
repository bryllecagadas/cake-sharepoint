(function($) {

	var defaults = {
		acoAlias: 'files',
		permissionsUrl: null,
		downloadUrl: null,
		secureId: null,
		selected: null,
		sessionId: null,
		uploadUrl: null,
		url: null
	};

	var Files = window.Files || defaults;
	
	Files.permissions = {};

	Files.plugins = ['types', 'contextmenu', 'wholerow'];
	Files.defaultPlugins = Files.plugins.slice(0);

	Files.disable = function() {
		if (typeof Files.jstree != 'undefined') {
			var jstree = Files.jstree.data('jstree');
			jstree.disable_node(jstree.get_node('#').children_d);
		}
	};

	Files.enable = function() {
		if (typeof Files.jstree != 'undefined') {
			var jstree = Files.jstree.data('jstree');
			jstree.enable_node(jstree.get_node('#').children_d);
		}
	};

	Files.init = function() {
		if (!Files.url  || !Files.secureId) {
			return false;
		}

		Files.init_jstree();
		
		$('.save-tree-options').click(function() {
			if (typeof Files.jstree != 'undefined') {
				var jstree = Files.jstree.data('jstree');

				if ($.inArray('checkbox', jstree.settings.plugins) !== -1) { 
					var items = jstree.get_node('#').children_d.slice();
					var item_values = {};

					for (var i in items) {
						item_values[items[i]] = {
							disabled: jstree.is_checked(items[i]) ? 0 : 1
						};
					}

					Files.request('save_role_setting', {
						items: item_values,
						role: Files.role
					});
				}
			}
			return false;
		});

		if (Files.sessionId && Files.uploadUrl) {
			Files.file_upload_init();
		}
	}

	Files.file_upload_init = function() {
		$('#fileupload').fileupload({
			url: Files.uploadUrl,
			dataType: 'json',
			formData: function (form) {
				return [
					{
						name: 'session',
						value: Files.sessionId
					},
					{
						name: 'project_id',
						value: Files.secureId
					},
					{
						name: 'destination',
						value: Files.selected ? Files.selected : ''
					}
				];
			}
		}).bind('fileuploaddone', function(e, data) {
			var jstree = Files.jstree.data('jstree');
			var node;
			if (Files.selected && (node = jstree.get_node(Files.selected))) {
				jstree.refresh_node(node);
			}
		});
	};

	Files.set_destination = function(node) {
		var destination = node.id;

		if (node.type != 'folder') {
			destination = node.parent;
		}

		Files.selected = destination;
		var filter = function(destination) {
			return destination.substr(Files.acoAlias.length + 1);
		};
		$('.destination').html(filter(destination));
	};

	Files.init_jstree = function() {
		Files.jstree = $('#tree').on('ready.jstree', function (e, data) {
			var jstree = Files.jstree.data('jstree');
			var items = jstree.get_node('#').children_d;
			var dom;

			if ($.inArray('checkbox', jstree.settings.plugins) !== -1) {
				jstree.check_node('#');

				for (var i in items) {
					var node = jstree.get_node(items[i]);
					var uncheck = false;
					if (typeof node.original != 'undefined') {
						var original = node.original;
						if (typeof original.disabled != 'undefined' && original.disabled) {
							uncheck = true;
						}
					}

					dom = jstree.get_node(items[i], true)
						.children('.jstree-anchor')
						.children('.jstree-checkbox')
						.addClass('glyphicon glyphicon-unchecked');

					if (uncheck) {
						jstree.uncheck_node(items[i]);
					} else {
						jstree.check_node(items[i]);
					}
				}

				jstree.open_all();
			}

			Files.set_destination(jstree.get_node(jstree.get_node('#').children[0]));
		})
		.jstree({
			core: {
				data : {
					url: Files.url,
					method: 'POST',
					data: function(node) { 
						var data = {
							node_id: node.id,
							project_id: Files.secureId
						};

						if (typeof Files.role != 'undefined') {
							data.role = Files.role;
						}
						return data;
					}
				},
				check_callback : true,
				themes : {
					responsive : false,
					variant : 'large',
					stripes : false
				}
			},
			sort : function(a, b) {
				return this.get_type(a) === this.get_type(b) ? (this.get_text(a) > this.get_text(b) ? 1 : -1) : (this.get_type(a) >= this.get_type(b) ? 1 : -1);
			},
			contextmenu : {
				items : function(node) {
					var tmp = $.jstree.defaults.contextmenu.items();
					if (typeof Files.permissions[node.id] == 'undefined') {
						$.ajax({
							url: Files.permissionsUrl,
							type: 'POST',
							data: {
								project_id: Files.secureId,
								node_id: node.id,
								type: 'contextmenu'
							},
							dataType: 'JSON',
							success: function(response) {
								Files.permissions[node.id] = response;
								Files.jstree.data('jstree').show_contextmenu(node);
							}
						});

						tmp = {
							loading: {
								_disable: true,
								label: 'Loading...'
							}
						};

					} else {
						tmp.create.label = "New";
						delete tmp.create.action;
						tmp.create.submenu = {
							create_folder : {
								separator_after	: true,
								label : "Folder",
								action : function (data) {
									var inst = $.jstree.reference(data.reference),
										obj = inst.get_node(data.reference);
									var name = "New folder";
									inst.create_node(obj, { id : obj.id + '/' + name, type : "folder", text : name }, "last", function (new_node) {
										setTimeout(function () { inst.edit(new_node); },0);
									});
								}
							}
						};

						tmp.refresh = {
							label: 'Refresh',
							action: function(data) {
								var inst = $.jstree.reference(data.reference);
								var obj = inst.get_node(data.reference);
								inst.refresh_node(data.reference);
							}
						};

						for (var i in tmp) {
							if (!Files.permissions[node.id][i]) {
								delete tmp[i];
							}
						}

						if(this.get_type(node) === "file") {
							if (typeof tmp.create != 'undefined') {
								delete tmp.create;
							}

							if (typeof tmp.refresh != 'undefined') {
								delete tmp.refresh;
							}

							tmp.download = {
								label: 'Download',
								action: function(data) {
									var inst = $.jstree.reference(data.reference);
									var obj = inst.get_node(data.reference);
									Files.request('download_token', {
										id: obj.id
									}, data, function(response, data) {
										if (typeof response.token != 'undefined') {
											var url = Files.downloadUrl + '/' + Files.secureId + '/' + response.token
											window.location.href = url;
										}
									});
								}
							};
						}
					}

					return tmp;
				}
			},
			types : {
				default : { 
					icon : 'glyphicon glyphicon-file' 
				},
				file : { 
					valid_children : [], icon : 'glyphicon glyphicon-file'
				},
				folder : { 
					icon : 'glyphicon glyphicon-folder-close' 
				}
			},
			checkbox : {
				keep_selected_style : false
			},
			plugins : Files.plugins
		})
		.on('before_open.jstree', function (e, data) {
			var inst = data.instance;
			var dom = inst.get_node(data.node, true);
			dom
				.children('.jstree-anchor')
				.children('.glyphicon-folder-close')
				.removeClass('glyphicon-folder-close')
				.addClass('glyphicon-folder-open');
		})
		.on('after_close.jstree', function (e, data) {
			var inst = data.instance;
			var dom = inst.get_node(data.node, true);
			dom
				.children('.jstree-anchor')
				.children('.glyphicon-folder-open')
				.removeClass('glyphicon-folder-open')
				.addClass('glyphicon-folder-close');
		})
		.on('select_node.jstree', function (e, data) {
			// var inst = data.instance;
			// var dom = inst.get_node(data.node, true);
			// dom
			// 	.children('.jstree-anchor')
			// 	.children('.jstree-checkbox')
			// 	.removeClass('glyphicon-unchecked')
			// 	.addClass('glyphicon glyphicon-check');
		})
		.on('deselect_node.jstree', function (e, data) {
			// var inst = data.instance;
			// var dom = inst.get_node(data.node, true);
			// dom2
			// 	.children('.jstree-anchor')
			// 	.children('.jstree-checkbox')
			// 	.removeClass('glyphicon-check')
			// 	.addClass('glyphicon glyphicon-unchecked');
		})
		.on('changed.jstree', function (e, data) {
			Files.set_destination(data.node);
		})
		.on('delete_node.jstree', function (e, data) {
			Files.request('delete', {
				id: data.node.id
			}, data);
		})
		.on('create_node.jstree', function (e, data) {
			Files.request('create', {
				path: data.node.text,
				parent: data.node.parent,
				node: data.node
			}, data);
		})
		.on('rename_node.jstree', function (e, data) {
			Files.request('rename', {
				id: data.node.id,
				new_name: data.text,
				old_name: data.old,
				parent: data.node.parent
			}, data, function(response, data) {
				var inst = data.instance;
				inst.refresh_node(data.node.parent);
			});
		})
		.on('move_node.jstree', function (e, data) {
			Files.request('move', {
				id: data.node.id,
				new_parent: data.parent,
				old_parent: data.old_parent,
				parents: data.node.parents
			}, data);
		})
		.on('copy_node.jstree', function (e, data) {
			Files.request('copy', {
				id: data.original.id,
				new_parent: data.parent
			}, data);
		});
	};

	Files.request = function(action, data, original_data, callback) {
		data.project_id = Files.secureId;
		data.action = action;

		Files.disable();

		$.ajax({
			url: Files.url,
			dataType: 'json',
			type: 'POST',
			data: data,
			success: function(response) {
				Files.enable();
				if (typeof callback === 'function') {
					callback(response, original_data, data);
				}
			}
		})
	};

	$(document).ready(function() {
		Files.init();

		if ($('.role-switcher').length) {
			$('.role-switcher .role').click(function() {
				if (typeof Files.jstree != 'undefined') {
					var jstree = Files.jstree.data('jstree');
					var role = $(this).data('role');

					if (typeof Files.role != 'undefined' && Files.role == role) {
						Files.plugins = Files.defaultPlugins.slice(0);
						delete Files.role;
					} else {
						Files.role = $(this).data('role');
						if ($.inArray('checkbox', Files.plugins) == -1) {
							Files.plugins.push('checkbox');
						}
					}

					Files.disable();
					jstree.destroy(true);
					Files.init_jstree();
				}
				return false;
			});
		}
	});
})(jQuery);