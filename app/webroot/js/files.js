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

	Files.plugins = ['types', 'contextmenu', 'wholerow', 'grid'];
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
					var node;

					for (var i in items) {
						node = jstree.get_node(items[i]);
						item_values[Files.get_path(node)] = {
							disabled: jstree.is_checked(items[i]) ? 0 : 1
						};
					}

					Files.request('save_role_setting', {
						items: item_values,
						role: Files.role
					}, {}, function() {
						Files.disable();
						jstree.destroy(true);
						Files.init_jstree();
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
		})
		.on('fileuploaddone', function(e, data) {
			var jstree = Files.jstree.data('jstree');
			var node;
			
			if (Files.selectedId && (node = jstree.get_node(Files.selectedId))) {
				jstree.refresh_node(node);
			}
		})
		.on('fileuploaddestroy', function(e, data) {
			console.log(data);
		});
	};

	Files.set_destination = function(node) {
		var destination = node.data.path;
		Files.selectedId = node.id;
		if (node.type != 'folder') {
			var parent = Files.jstree.data('jstree').get_node(node.parent);
			destination = Files.get_path(parent);
			Files.selectedId = parent.id;
		}

		Files.selected = destination;
		
		var filter = function(destination) {
			return destination.substr(Files.acoAlias.length + 1);
		};
		$('.destination').html(filter(destination));
	};

	Files.get_path = function(node) {
		if (typeof node.data === 'undefined') {
			return node.id;
		}
		return node.data.path;
	};

	Files.refresh_parent = function(node) {
		var instance = Files.jstree.data('jstree');
		if (node.parent == '#') {
			instance.refresh();
		} else {
			instance.refresh_node(node.parent);
			var target = instance.get_node(node || "#", true);
			instance._prepare_grid(target);
		}
		instance.redraw_node(node);
	};

	Files.set_check_status = function(items) {
		var jstree = Files.jstree.data('jstree');
		for (var i in items) {
			var node = jstree.get_node(items[i]);
			var uncheck = false;
			if (typeof node.original != 'undefined') {
				var original = node.original;
				if (typeof original.disabled != 'undefined' && original.disabled) {
					uncheck = true;
				}
			}

			if (uncheck) {
				jstree.uncheck_node(items[i]);
			} else {
				jstree.check_node(items[i]);
			}
		}
	};

	Files.download = function(node) {
		Files.request('download_token', {
			id: node.data.path
		}, {}, function(response) {
			if (typeof response.token != 'undefined') {
				var url = Files.downloadUrl + '/' + Files.secureId + '/' + response.token
				window.location.href = url;
			}
		});
	}

	Files.init_jstree = function() {
		Files.jstree = $('#tree').on('ready.jstree', function (e, data) {
			var jstree = Files.jstree.data('jstree');
			var items = jstree.get_node('#').children_d;
			var dom;

			if ($.inArray('checkbox', jstree.settings.plugins) !== -1) {
				jstree.check_node('#');
				Files.set_check_status(items);
				jstree.open_all();
			}

			Files.set_destination(jstree.get_node(jstree.get_node('#').children[0]));
		})
		.jstree({
			core: {
				data : {
					url: Files.url,
					method: 'POST',
					dataType: 'json',
					data: function(node) { 
						var data = {
							node_id: Files.get_path(node),
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
				select_node: false,
				items : function(node) {
					var tmp = $.jstree.defaults.contextmenu.items();
					if (typeof Files.permissions[node.data.path] == 'undefined') {
						$.ajax({
							url: Files.permissionsUrl,
							type: 'POST',
							data: {
								project_id: Files.secureId,
								node_id: node.data.path,
								type: 'contextmenu'
							},
							dataType: 'JSON',
							success: function(response) {
								Files.permissions[node.data.path] = response;
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
									inst.create_node(obj, { data: { path : obj.data.path + '/' + name }, type : "folder", text : name }, "last", function (new_node) {
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
							if (!Files.permissions[node.data.path][i]) {
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
									Files.download(obj);
								}
							};
						}

						if ($.isEmptyObject(tmp)) {
							$.vakata.context.hide();
						}
					}

					return tmp;
				}
			},
			types : {
				'default' : { 
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
				keep_selected_style : false,
				three_state: false,
				cascade: 'down',
				whole_node: false,
				tie_selection: false
			},
			grid: {
				columns: [
					{
						width: 50,
						header: 'File'
					},
					{
						width: 20,
						header: 'Modified',
						value: function (node) {
							return node.db_created ? node.db_created : (node.created ? node.created : '');
						}
					},
					{
						width: 20,
						header: 'Modified by',
						value: function (node) {
							return node.db_user ? node.db_user : '';
						}
					}
				]
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
			Files.set_destination(data.node);
			var inst = data.instance;
			if (data.node.type == 'folder') {
				if (inst.is_open(data.node)) {
					inst.close_node(data.node);
				} else {
					inst.open_node(data.node);
				}
			} else {
				Files.download(data.node);
			}
		})
		.on('deselect_node.jstree', function (e, data) {
		})
		.on('changed.jstree', function (e, data) {
		})
		.on('delete_node.jstree', function (e, data) {
			Files.request('delete', {
				id: data.node.data.path
			}, data);
			Files.refresh_parent(data.node);
		})
		.on('create_node.jstree', function (e, data) {
			var inst = data.instance;
			Files.request('create', {
				path: data.node.text,
				parent: Files.get_path(inst.get_node(data.node.parent)),
				node: data.node
			}, data);
		})
		.on('rename_node.jstree', function (e, data) {
			var inst = data.instance;
			Files.request('rename', {
				id: data.node.data.path,
				new_name: data.text,
				old_name: data.old,
				parent: Files.get_path(inst.get_node(data.node.parent))
			}, data, function(response, data) {
				var inst = data.instance;
				inst.refresh_node(data.node.parent);
			});
		})
		.on('move_node.jstree', function (e, data) {
			var inst = data.instance;
			var parents = [];
			var node;

			for (var i in data.node.parents) {
				node = inst.get_node(data.node.parents[i]);
				parents.push(Files.get_path(node));
			}

			Files.request('move', {
				id: data.node.data.path,
				new_parent: inst.get_node(data.parent).data.path,
				old_parent: inst.get_node(data.old_parent).data.path,
				parents: parents
			}, data);
		})
		.on('copy_node.jstree', function (e, data) {
			var inst = data.instance;
			Files.request('copy', {
				id: data.original.data.path,
				new_parent: inst.get_node(data.parent).data.path
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
					var refresh = false;
					var loading = jstree.is_loading('#');

					if (!loading && (role == 'admin' || !role) && typeof Files.role != 'undefined') {
						Files.plugins = Files.defaultPlugins.slice(0);
						delete Files.role;
						refresh = true;
						$(this).closest('.nav-tabs').next().hide();
					} else if (!loading && role != 'admin' && Files.role != role) {
						Files.role = $(this).data('role');
						if ($.inArray('checkbox', Files.plugins) == -1) {
							Files.plugins.push('checkbox');
						}
						refresh = true;
						$(this).closest('.nav-tabs').next().show();
					}

					if (refresh) {
						Files.disable();
						jstree.destroy(true);
						Files.init_jstree();

						$(this).closest('.nav-tabs').children('.active').removeClass('active');
						$(this).parent().addClass('active');
					}
				}
				return false;
			});
		}
	});
})(jQuery);