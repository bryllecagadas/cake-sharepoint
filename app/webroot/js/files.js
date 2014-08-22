(function($) {

	var Files = window.Files || {};
	
	Files.permissions = {};

	Files.plugins = ['types', 'contextmenu'];
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
		if (typeof Files.url === 'undefined' || typeof Files.secureId === 'undefined') {
			return false;
		}

		Files.init_jstree();
		$('.save-tree-options').click(function() {
			if (typeof Files.jstree != 'undefined') {
				var jstree = Files.jstree.data('jstree');
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
			return false;
		});
	}

	Files.init_jstree = function() {
		Files.jstree = $('#tree').on('ready.jstree', function (e, data) {
			var jstree = Files.jstree.data('jstree');
			var items = jstree.get_node('#').children_d;

			if ($.inArray('checkbox', jstree.settings.plugins) === -1) {
				return;
			}

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

				if (uncheck) {
					jstree.uncheck_node(items[i]);
				} else {
					jstree.check_node(items[i]);
				}
			}

			jstree.open_all();
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
					variant : 'small',
					stripes : true
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
									inst.create_node(obj, { type : "folder", text : "New folder" }, "last", function (new_node) {
										setTimeout(function () { inst.edit(new_node); },0);
									});
								}
							},
							create_file : {
								label	: "File",
								action : function (data) {
								}
							}
						};

						for (var i in tmp) {
							if (!Files.permissions[node.id][i]) {
								delete tmp[i];
							}
						}
					}

					return tmp;
				}
			},
			types : {
				default : { 
					icon : 'jstree-file' 
				},
				file : { 
					valid_children : [], icon : 'jstree-file' 
				},
				folder : { 
					icon : 'jstree-folder' 
				}
			},
			plugins : Files.plugins
		})
		.on('changed.jstree', function (e, data) {
		})
		.on('delete_node.jstree', function (e, data) {
			Files.request('delete', {
				id: data.node.id
			});
		})
		.on('create_node.jstree', function (e, data) {
			Files.request('create', {
				path: data.node.text,
				parent: data.node.parent,
				node: data.node
			});
		})
		.on('rename_node.jstree', function (e, data) {
			Files.request('rename', {
				id: data.node.id,
				new_name: data.text,
				old_name: data.old,
				parent: data.node.parent
			});
		})
		.on('move_node.jstree', function (e, data) {
			Files.request('move', {
				id: data.node.id,
				new_parent: data.parent,
				old_parent: data.old_parent,
				parents: data.node.parents
			});
		})
		.on('copy_node.jstree', function (e, data) {
			Files.request('copy', {
				id: data.original.id,
				new_parent: data.parent
			});
		});
	};

	Files.request = function(action, data, callback) {
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