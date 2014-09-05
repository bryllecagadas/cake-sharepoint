<?php

App::uses('File', 'Utility');
App::uses('Folder', 'Utility');
App::uses('Hash', 'Utility');
App::uses('Security', 'Utility');

class ProjectAclComponent extends Component {
	public $acoAlias = 'files';

	public $components = array(
		'Acl',
		'Auth',
		'LogHandler',
		'ProjectUpload',
	);

	public $highlightRole = null;
	public $highlightType = null;

	public $permissionDefaults = array(
		'project_manager' => '*',
		'consultant' => 'read',
		'client' => 'read',
	);

	public $projectModel = 'Project';

	public $secureProjectId;

	private $project;

	private $project_dirs = array();

	private $project_paths = array();

	private $projectId;

	public function acoCreate($path) {
		$parts = explode("/", $path);
		$alias = $parts[count($parts) - 1];
		unset($parts[count($parts) - 1]);
		$parent = $this->Acl->Aco->node(implode("/", $parts));

		if ($parent) {
			$this->Acl->Aco->create(array('parent_id' => $parent[0]['Aco']['id'], 'model' => null, 'alias' => $alias));
			return $this->Acl->Aco->save();
		}

		return false;
	}

	public function aroCreate($aro_alias) {
		$node = $this->Acl->Aro->node($aro_alias);

		if (!$node) {
			$this->Acl->Aro->create();
			$node = $this->Acl->Aro->save(array('alias' => $aro_alias));
		}

		return $node;
	}

	public function checkPermission($data = array()) {
		$return = array();

		if (!isset($data['type'])) {
			return;
		}

		$type = $data['type'];

		switch ($type) {
			case 'contextmenu':
				$id = $data['node_id'];
				$path = WWW_ROOT . $this->preparePath($id);

				$actions = array(
					'ccp' => 'update', 
					'create' => 'create',
					'remove' => 'delete',
					'rename' => 'update',
					'refresh' => 'read',
					'download' => 'read',
				);

				foreach ($actions as $menuitem => $action) {
					$return[$menuitem] = $this->userAccess($path, $action);
				}

				break;
		}

		return $return;
	}

	public function defaultPermissions($aco_path) {
		$project = $this->project;

		$this->Acl->allow('1', $aco_path);
		foreach ($this->roles() as $name => $role) {
			$aro_alias = $this->generateProjectRoleAlias($this->projectId, $role['Role']['id']);
			$this->aroCreate($aro_alias);
			$permission = isset($this->permissionDefaults[$name]) ? $this->permissionDefaults[$name] : '';
			if ($permission && ($name == 'project_manager' || $this->isRootPath($aco_path))) {
				$this->Acl->allow($aro_alias, $aco_path, $permission);
			} else {
				$this->Acl->deny($aro_alias, $aco_path, $permission);
			}
		}
	}

	public function dirCreate($dir, $parents = array()) {
		$base = $this->projectDir();
		$path = WWW_ROOT . $this->acoAlias . DS . $this->projectDir(true);
		$aco_path = $this->acoAlias . '/' . $base;

		$path .= ($parents ? DS . $this->generateParentPath($parents, true) : '') . DS . $dir;
		$aco_path .= ($parents ? '/' . $this->generateParentPath($parents) : '') . '/' . $dir;

		$folder = new Folder();
		$result = file_exists($path) ? TRUE : $folder->create($path);

		if ($result) {
			if ($this->acoCreate($aco_path)) {
				$this->defaultPermissions($aco_path);
			}
		}

		return $result;
	}

	public function download(&$response, $path) {
		$file_path = $this->preparePath($path);
		$full_path = WWW_ROOT . $file_path;

		if ($this->userAccess($full_path, 'read') && is_file($full_path)) {
			$this->LogHandler->log('Project', 'A file was downloaded.', array(
				'project_id' => $this->projectId,
				'file' => basename($full_path),
			));
	    return $response->file($full_path, array('download' => true, 'name' => basename($full_path)));
		}

		return false;
	}

	public function downloadToken($aco_id) {
		$path = $this->preparePath($aco_id);

		if (is_file(WWW_ROOT . $path)) {
			$user = $this->Auth->user();
			$token = Security::hash($aco_id . (int) TIME_START);
			$Token = ClassRegistry::init('Token');
			$Token->save(array(
				'token' => $token,
				'user_id' => $user['id'],
				'project_id' => $this->projectId,
				'path' => $aco_id,
			));
			return $token;
		}

		return false;
	}

	public function generateParentPath($parents, $file = false) {
		return implode($file ? DS : '/', $parents);
	}

	public function generateProjectRoleAlias($project_id, $role_id) {
		return $project_id . ':' . $role_id;
	}

	public function highlightRole($role, $type) {
		$this->highlightRole = $role;
		$this->highlightType = $type;
	}

	public function initialize(Controller $controller) {
		if (!$this->Acl->Aco->find('first', array('conditions' => array('alias' => $this->acoAlias)))) {
			$this->Acl->Aco->create(array('parent_id' => null, 'alias' => $this->acoAlias));
			$this->Acl->Aco->save();
		}

		if (!$this->Acl->Aro->find('first', array('conditions' => array('alias' => '1')))) {
			$this->Acl->Aro->create(array('parent_id' => null, 'alias' => '1'));
			$this->Acl->Aro->save();
		}

		if (!$this->Acl->Aro->find('first', array('conditions' => array('alias' => '0')))) {
			$this->Acl->Aro->create(array('parent_id' => null, 'alias' => '0'));
			$this->Acl->Aro->save();
		}
	}

	public function isRootPath($path) {
		return $path == $this->acoAlias . '/' . $this->projectDir();
	}

	public function modifyName($old_project, $new_project) {
		$this->setProject($old_project);
		$old_aco = $this->acoAlias . '/' . $this->projectDir();
		$old_dir = WWW_ROOT . $this->acoAlias . DS . $this->projectDir(true);
		$this->setProject($new_project, true);
		$new_aco = $this->acoAlias . '/' . $this->projectDir();
		$new_dir = WWW_ROOT . $this->acoAlias . DS . $this->projectDir(true);

		if (rename($old_dir, $new_dir)) {
			$node = $this->Acl->Aco->node($old_aco);
			if ($node) {
				$node[0]['Aco']['alias'] = $this->projectDir();
				$this->Acl->Aco->save($node[0]);
			}
		}
	}

	public function parentsFromJs($parents = array()) {
		$parents = array_reverse($parents);
		unset($parents[0]);
		unset($parents[1]);
		return $parents; 
	}

	public function pathFromId($id, $type = 'aco', $file = false) {
		$type = ucwords($type);
		$parent_id = $id;
		$separator = $file ? DS : '/';
		$paths = array();

		while($parent_id && ($node = $this->Acl->{$type}->findByid($parent_id))) {
			array_unshift($paths, $node[$type]['alias']);
			$parent_id = $node[$type]['parent_id'];
		}

		return implode($separator, $paths);
	}

	public function preparePath($path, $full = false, $reverse = false) {
		$search = $this->acoAlias . DS . ($reverse ? $this->projectDir(true) : $this->projectDir());
		$replace = $this->acoAlias . DS . ($reverse ? $this->projectDir() : $this->projectDir(true));

		if ($full) {
			$search = WWW_ROOT . $search;
			$replace = WWW_ROOT . $replace;
		}

		return str_replace($search, $replace, $path);
	}

	public function processAction($action, $data) {
		$response = array();
		switch ($action) {
			case 'create':
				if (!$this->userAccess(WWW_ROOT . $this->preparePath($data['parent']), 'create')) {
					return;
				}
				$parents = explode('/', $data['parent']);
				unset($parents[0]);
				unset($parents[1]);
				if ($this->dirCreate($data['path'], $parents)) {
					$this->LogHandler->log('Project', 'A directory was added to the project.', array(
						'project_id' => $this->projectId,
						'parent' => $data['parent'],
						'path' => $data['path']
					));
				}
				break;

			case 'rename':
				$parents = explode('/', $data['parent']);
				unset($parents[0]);
				unset($parents[1]);
				$old_path = WWW_ROOT . $this->acoAlias . DS . $this->projectDir(true);
				$old_aco_path = $this->acoAlias . '/' . $this->projectDir();

				if ($parents) {
					$old_path .= DS . $this->generateParentPath($parents, true);
					$old_aco_path .= '/' . $this->generateParentPath($parents);
				}
				
				$new_path = $old_path . DS . $data['new_name'];
				$old_path .= DS . $data['old_name'];
				$old_aco_path .= '/' . $data['old_name'];

				if (
					$this->isRootPath($old_aco_path) || 
					!$this->userAccess($old_path, 'update') || 
					!$this->userAccess(WWW_ROOT . $this->preparePath($data['parent']), 'create')
				) {
					return;
				}

				if (file_exists($new_path)) {
					$response['error'] = 'Cannot rename, file with the same name already exists.';
				} else {
					if (rename($old_path, $new_path)) {
						$node = $this->Acl->Aco->node($old_aco_path);
						$node[0]['Aco']['alias'] = $data['new_name'];
						$this->Acl->Aco->save($node[0]);

						$this->LogHandler->log('Project', 'A directory were renamed.', array(
							'project_id' => $this->projectId,
							'old_path' => $old_aco_path,
							'new_path' => $new_path
						));
					}
				}
				
				break;

			case 'move':
				$aco = $this->Acl->Aco->node($data['id']);
				$old_path = WWW_ROOT . $this->preparePath($data['id']);
				$new_path = WWW_ROOT . $this->preparePath($data['new_parent']) . DS . $aco[0]['Aco']['alias'];

				if (
					$this->isRootPath($data['id']) || 
					!$this->userAccess($old_path, 'update') || 
					!$this->userAccess(WWW_ROOT . $this->preparePath($data['new_parent']), 'create')
				) {
					return;
				}

				if (file_exists($new_path)) {
					$response['error'] = 'Cannot rename, file with the same name already exists.';
				} else if(!rename($old_path, $new_path)) {
					$response['error'] = 'File cannot be moved.';
				} else {				
					$parent = $this->Acl->Aco->node($data['new_parent']);
					$aco[0]['Aco']['parent_id'] = $parent[0]['Aco']['id'];
					$this->Acl->Aco->save($aco[0]);

					$this->LogHandler->log('Project', 'A directory were moved.', array(
						'project_id' => $this->projectId,
						'old_path' => $data['id'],
						'destination' => $data['new_parent']
					));
				}
				
				break;

			case 'copy':
				$aco = $this->Acl->Aco->node($data['id']);
				$old_path = WWW_ROOT . $this->preparePath($data['id']);
				$new_path = WWW_ROOT . $this->preparePath($data['new_parent']);

				if (
					$this->isRootPath($data['id']) || 
					!$this->userAccess($old_path, 'read') || 
					!$this->userAccess($new_path, 'create')
				) {
					return;
				}

				if (file_exists($new_path . DS . $aco[0]['Aco']['alias'])) {
					$response['error'] = 'Cannot copy file, file with the same name already exists.';
				} else if (is_file($old_path) && !copy($old_path, $new_path . DS . $aco[0]['Aco']['alias'])) {
					$response['error'] = 'Cannot copy file.';
				} else if (is_dir($old_path) && !$this->recurseCopyDir($old_path, $new_path . DS . $aco[0]['Aco']['alias'])) {
					$response['error'] = 'Cannot copy directory.';
				} else {
					$aco_path = str_replace(WWW_ROOT, '', $this->preparePath($new_path, true, true)) . $aco[0]['Aco']['alias'];
					$aco_path = str_replace(DS, '/', $aco_path);
					$this->acoCreate($aco_path);

					$this->LogHandler->log('Project', 'A directory were copied.', array(
						'project_id' => $this->projectId,
						'old_path' => $data['id'],
						'destination' => $data['new_parent']
					));
				}

				break;

			case 'delete':
				$aco = $this->Acl->Aco->node($data['id']);
				$delete_path = WWW_ROOT . $this->preparePath($data['id']);

				if ($this->isRootPath($data['id']) || !$this->userAccess($delete_path, 'delete')) {
					return;
				}

				if (is_file($delete_path) && !unlink($delete_path)) {
					$response['error'] = 'There was an error deleting the file.';
				} else if (is_dir($delete_path)) {
					$this->rrmdir($delete_path);
					$this->Acl->Aco->delete($aco[0]['Aco']['id']);

					$this->LogHandler->log('Project', 'A directory were deleted.', array(
						'project_id' => $this->projectId,
						'path' => $data['id'],
					));
				}

				break;

			case 'save_role_setting':
				$items = $data['items'];
				$role = $data['role'];
				$user = $this->Auth->user();

				if ($user['admin'] || in_array('project_manager', $this->userProjectRoles())) {
					$this->saveRolePermissions($items, $role);

					$this->LogHandler->log('Project', 'Role view settings were modified..', array(
						'project_id' => $this->projectId,
						'role' => $role,
						'paths' => $items,
					));
				}
				break;

			case 'download_token':
				$path = WWW_ROOT . $this->preparePath($data['id']);
				if ($this->userAccess($path, 'read')) {
					$response['token'] = $this->downloadToken($data['id']);
				}
				break;
		}

		return $response;
	}

	public function projectDir($path = false) {
		$project = $this->project;
		$dir = false;

		if (!isset($this->project_dirs[$this->projectId])) {
			$this->project_dirs[$this->projectId] = Inflector::slug(strtolower($project[$this->projectModel]['name'])) . 
					'_' . $project[$this->projectModel]['id'];
			$this->project_paths[$this->projectId] = Security::hash($this->project_dirs[$this->projectId]);
		}

		return $path ? $this->project_paths[$this->projectId] : $this->project_dirs[$this->projectId];
	}

	public function projectFiles($node_id = NULL) {
		$base = $this->projectDir();
		$parent = WWW_ROOT . $this->acoAlias . DS . $this->projectDir(true);
	
		if ($node_id && $node_id != "#") {
			$path = $node_id;//$this->pathFromId($node_id);
			$node = $this->Acl->Aco->node($path);
			$parent = WWW_ROOT . $this->preparePath($path);
			$folder = new Folder($parent);

			$contents = $this->readRecursive($folder, $parent, NULL, false);
		} else {
			$folder = new Folder($parent);

			// Getting aco data
			$node = $this->Acl->Aco->node($this->acoAlias . DS . $this->projectDir());

			$contents = array(
				array(
					'text' => $this->project['Project']['name'],
					'children' => $this->readRecursive($folder, $parent, NULL, false),
					'data' => array(
						'path' => $this->acoAlias . DS . $this->projectDir(),
					),
					'type' => 'folder',
				)
			);
		}

		return $contents;
	}

	public function projectInit($project) {
		$this->setProject($project);
		$dir = $this->projectDir();
		$folder = new Folder();
		$path = WWW_ROOT . $this->acoAlias . DS . $this->projectDir(true);

		if (!file_exists($path)) {
			if ($folder->create($path)) {
				$created = TRUE;
			} else {
				$created = false;
			}
		} else {
			$created = TRUE;
		}

		if ($created) {
			$aco_path = $this->acoAlias . '/' . $dir;
			$node = $this->Acl->Aco->node($aco_path);

			if (!$node) {
				$parent = $this->Acl->Aco->node($this->acoAlias);
				$this->Acl->Aco->create(array('parent_id' => $parent[0]['Aco']['id'], 'model' => null, 'alias' => $dir));
				$this->Acl->Aco->save();
			}

			$this->defaultPermissions($aco_path);
		}

		return $created;
	}

	public function readRecursive(&$folder, $parent, $path = NULL, $recurse = true) {
		$contents = array();

		$new_parent = $parent;
		if ($path) {
			$folder->cd($path);
			$new_parent .= DS . $path;
		}

		foreach ($folder->read() as $type => $content) {
			$type = $type ? 'file' : 'folder';
			foreach ($content as $path) {

				// Normalize path
				$aco_path = str_replace(WWW_ROOT . $this->acoAlias . DS . $this->projectDir(true), '', $new_parent . DS . $path);
				$aco_path = $this->acoAlias . '/' . $this->projectDir() . $aco_path;
				$node = $this->Acl->Aco->node($aco_path);

				if (!$node) {
					if ($this->acoCreate($aco_path)) {
						$node = $this->Acl->Aco->node($aco_path);
						$this->defaultPermissions($aco_path);
					}
				}

				if (!$this->userAccess($new_parent . DS . $path, 'read')) {
					continue;
				}

				$stat = stat($new_parent . DS . $path);
				$FileInfo = ClassRegistry::init('FileInfo');
				$file_info = $FileInfo->findByaco_id($node[0]['Aco']['id']);

				$item = array(
					'children' => false,
					'text' => $path,
					'data' => array(
						'path' => $aco_path,
						'created' => date('d/m/Y h:i A', $stat['ctime']),
						'modified' => date('d/m/Y h:i A', $stat['mtime']),
						'db_created' => $file_info ? date('d/m/Y h:i A', strtotime($file_info['FileInfo']['created'])) : '',
						'db_user' => $file_info && isset($file_info['User']) ? $file_info['User']['username'] : ''
					),
					'type' => $type,
				);

				if ($this->highlightRole) {
					$item['disabled'] = !$this->userAccess($new_parent . DS . $path, 'read', $this->highlightRole);
				}

				if (file_exists($new_parent . DS . $path)) {
					if ($type == 'folder') {
						$item['children'] = true;
						if ($recurse && $children = $this->readRecursive($folder, $new_parent, $path, $recurse)) {
							$item['children'] = $children;
						}
					} else {
						$item['filetype'] = filetype($new_parent . DS . $path);
					}
				}

				$contents[] = $item;
			}
		}

		$folder->cd($parent);
		return $contents;
	}

	public function recurseCopyDir($src, $dst) {
		$dir = opendir($src); 
		@mkdir($dst); 
		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
					$this->recurseCopyDir($src . '/' . $file,$dst . '/' . $file); 
				} else { 
					copy($src . '/' . $file,$dst . '/' . $file); 
				} 
			} 
		} 
		closedir($dir); 
		return true;
	}

	public function roles($as_id = false) {
		static $roles, $roles_ids;

		if (!isset($roles)) {
			$Role = ClassRegistry::init('Role');
			$roles = array();
			foreach ($Role->find('all') as $role) {
				$name = $role['Role']['name'];
				$roles[$name] = $role;
				$roles_ids[$role['Role']['id']] = $role;
			}
		}

		return $as_id ? $roles_ids : $roles;
	}

	public function rrmdir($dir) {
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   }
	}

	public function saveRolePermissions($items, $role) {
		$Role = ClassRegistry::init('Role');
		$permission = $this->permissionDefaults[$role];
		$role = $Role->findByname($role);
		$aro_alias = $this->generateProjectRoleAlias($this->projectId, $role['Role']['id']);

		$parent_aco_path = $this->acoAlias . '/' . $this->projectDir();
		foreach ($items as $aco_path => $item) {
			$deny = intval($item['disabled']);

			if ($parent_aco_path == $aco_path) {
				continue;
			}
			
			if ($deny && !$this->isRootPath($aco_path)) {
				if ($this->Acl->check($aro_alias, $aco_path, $permission)) {
					$this->Acl->deny($aro_alias, $aco_path, $permission);
				}
			} else {
				if (!$this->Acl->check($aro_alias, $aco_path, $permission)) {
					$this->Acl->allow($aro_alias, $aco_path, $permission);
				}
			}
		}
	}

	public function setProject($project, $reset = false) {
		$this->project = $project;
		$this->projectId = $this->project[$this->projectModel]['id'];
		$this->secureProjectId = Security::hash($this->projectId, null, true);

		if ($reset) {
			$this->project_dirs = array();
			$this->project_paths = array();
		}
	}

	public function uploadFiles($request, &$response) {
		$has_permission = $this->userProjectPermission();
		$response = null;
		$user = $this->Auth->user();

		if ($has_permission) {

			if ($upload_dir = $request->data('destination')) {
				$upload_dir = WWW_ROOT . $this->preparePath($upload_dir) . DS;
			} else {
				$upload_dir = WWW_ROOT . $this->acoAlias . DS . $this->projectDir(true) . DS;
			}

			$options = array(
	      'upload_dir' => $upload_dir,
			);

			$response = $this->ProjectUpload->process($request, $response, $options);

			if (isset($response['files'])) {
				$this->LogHandler->log('Project', 'Files were added to the project.', array(
					'project_id' => $this->projectId,
					'files' => $response['files']
				));

				foreach ($response['files'] as $file) {
					if (!empty($file->error)) {
						continue;
					}

					$aco_path = str_replace(WWW_ROOT, '', $this->preparePath($upload_dir, true, true)) . $file->name;
					$node = $this->Acl->Aco->node($aco_path);

					if (!$node) {
						if ($this->acoCreate($aco_path)) {
							$node = $this->Acl->Aco->node($aco_path);
							$this->defaultPermissions($aco_path);
							$FileInfo = ClassRegistry::init('FileInfo');
							$FileInfo->save(array(
								'user_id' => $user['id'],
								'aco_id' => $node[0]['Aco']['id']
							));
						}
					}

					$file->url = $aco_path;

					$file->deleteUrl = Router::url(array(
						'controller' => 'projects',
						'action' => 'files',
						'ajax' => true,
					));

					$file->data = http_build_query(array(
						'project_id' => $this->secureProjectId,
						'id' => $aco_path,
						'action' => 'delete',
						'source' => 'fileupload',
					));

					$file->deleteType = 'POST';
				}
			}
		}

		return $response;
	}

	public function userAccess($path, $action = 'read', $role = null) {
		static $permissions = array();

		if (!isset($permissions[$path]) || !isset($permissions[$path][$action])) {
			if (!isset($permissions[$path])) {
				$permissions[$path] = array();
			}

			$user = $this->Auth->user();
			$permissions[$path] = false;

			if ($user['admin'] && !$role) {
				$permissions[$path] = true;
			} else {

				// Normalize path
				$aco_path = str_replace(WWW_ROOT . $this->acoAlias . DS . $this->projectDir(TRUE), '', $path);
				$aco_path = $this->acoAlias . '/' . $this->projectDir() . $aco_path;
				
				$aro_checks = array();

				if ($role) {
					$Role = ClassRegistry::init('Role');
					$role = $Role->findByname($role);
					$aro_checks[] = $this->generateProjectRoleAlias($this->projectId, $role['Role']['id']);
				} else {
					$UserProjectRole = ClassRegistry::init('UserProjectRole');

					$options = array(
						'conditions' => array(
							'user_id' => $user['id'],
							'project_id' => $this->projectId,
						)
					);

					foreach ($UserProjectRole->find('all', $options) as $role) {
						$aro_checks[] = $this->generateProjectRoleAlias($this->projectId, $role['UserProjectRole']['role_id']);
					}
				}

				foreach ($aro_checks as $aro) {
					if ($this->Acl->check((string)$aro, $aco_path, $action)) {
						$permissions[$path] = true;
						break;
					}
				}
			}
		}

		return $permissions[$path];
	}

	public function userProjectPermission($user = null) {
		static $users = array();

		if (!$user) {
			$user = $this->Auth->user();
		}
		
		if (!isset($users[$user['id']])) {
			$user_project_roles = $this->userProjectRoles();
			$users[$user['id']] = $user['admin'] || in_array('project_manager', $user_project_roles);	
		}

		return $users[$user['id']];
	}

	public function userProjectRoles($user_id = null) {
		if (!$this->project) {
			return;
		}

		$User = ClassRegistry::init('User');
		$UserProjectRole = ClassRegistry::init('UserProjectRole');
		$user_roles = array();

		if ($user_id && ($user = $User->findByid($user_id))) {
			$user = $user['User'];
		} else {
			$user = $this->Auth->user();
		}

		if (!$user) {
			return;
		}
		
		$options = array(
			'conditions' => array(
				'user_id' => $user['id'],
				'project_id' => $this->projectId,
			)
		);

		$roles = $this->roles(TRUE);

		foreach ($UserProjectRole->find('all', $options) as $role) {
			$role_id = $role['UserProjectRole']['role_id'];
			$user_roles[] = $roles[$role_id]['Role']['name'];
		}

		return $user_roles;
	}
}