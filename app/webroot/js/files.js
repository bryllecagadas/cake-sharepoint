(function($) {

	var Files = window.Files || {};
	
	Files.permissions = {};

	Files.init = function() {
		if (typeof Files.url === 'undefined' || typeof Files.secureId === 'undefined') {
			return false;
		}

		Files.jstree = $('#tree').jstree({
			core: {
				data : {
					url: Files.url,
					method: 'POST',
					data: function(node) { 
						return {
							node_id: node.id,
							project_id: Files.secureId
						};
					}
				},
				check_callback : function(o, n, p, i, m) {
					if(m && m.dnd && m.pos !== 'i') { return false; }
					if(o === "move_node" || o === "copy_node") {
						if(this.get_node(n).parent === this.get_node(p).id) { return false; }
					}
					return true;
				},
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
						tmp.create.submenu = {
							create_folder : {
								separator_after	: true,
								label : "Folder",
								action : function (data) {
									var inst = $.jstree.reference(data.reference),
										obj = inst.get_node(data.reference);
									inst.create_node(obj, { type : "default", text : "New folder" }, "last", function (new_node) {
										setTimeout(function () { inst.edit(new_node); },0);
									});
								}
							},
							create_file : {
								label	: "File",
								action : function (data) {
									var inst = $.jstree.reference(data.reference),
										obj = inst.get_node(data.reference);
									inst.create_node(obj, { type : "file", text : "New file" }, "last", function (new_node) {
										setTimeout(function () { inst.edit(new_node); },0);
									});
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
					icon : 'file' 
				},
				file : { 
					valid_children : [], icon : 'file' 
				},
				folder : { 
					icon : 'folder' 
				}
			},
			plugins : ['types','contextmenu']
		})
		.on('changed.jstree', function (e, data) {
			// console.log(data.selected);
			if(data && data.selected && data.selected.length) {
				// console.log(data);
			}
		})
		.on('delete_node.jstree', function (e, data) {
			Files.request(data.instance, 'delete', {
				id: data.node.id
			});
		})
		.on('create_node.jstree', function (e, data) {
			Files.request(data.instance, 'create', {
				path: data.node.id,
				parent: data.node.parent
			});
		})
		.on('rename_node.jstree', function (e, data) {
			Files.request(data.instance, 'rename', {
				id: data.node.id,
				new_name: data.text,
				old_name: data.old,
				parent: data.node.parent
			});
		})
		.on('move_node.jstree', function (e, data) {
			Files.request(data.instance, 'move', {
				id: data.node.id,
				new_parent: data.parent,
				old_parent: data.old_parent,
				parents: data.node.parents
			});
		})
		.on('copy_node.jstree', function (e, data) {
			Files.request(data.instance, 'copy', {
				id: data.original.id,
				new_parent: data.parent
			});
		});
	}

	Files.request = function(instance, action, data) {
		data.project_id = Files.secureId;
		data.action = action;

		$.ajax({
			url: Files.url,
			dataType: 'json',
			type: 'POST',
			data: data,
			success: function(response) {
				console.log(response);
				// instance.refresh();
			}
		})
	};

	$(document).ready(function() {
		Files.init();
	});
})(jQuery);