<?php

class AclExtComponent extends Component {
	public $components = array(
		'Acl',
		'Auth',
		'File'
	);

	public $acoAlias = 'files';

	public $permissionDefaults = array(
		'project_manager' => '*',
		'consultant' => 'read',
		'client' => 'read',
	);

	public function aroCreate($aro_alias) {
		$node = $this->Acl->Aro->node($aro_alias);

		if (!$node) {
			$this->Acl->Aro->create();
			$node = $this->Acl->Aro->save(array('alias' => $aro_alias));
		}

		return $node;
	}

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

	public function defaultPermissions($project, $aco_path) {
		$project_id = $project[$this->File->projectModel]['id'];
		$base = $this->File->projectDir($project);

		$this->Acl->allow('1', $aco_path);
		foreach ($this->roles() as $name => $role) {
			$aro_alias = $this->generateProjectRoleAlias($project_id, $role['Role']['id']);
			$this->aroCreate($aro_alias);
			$permission = isset($this->permissionDefaults[$name]) ? $this->permissionDefaults[$name] : '';

			if ($permission) {
				$this->Acl->allow($aro_alias, $aco_path, $permission);
			}
		}
	}

	public function generateProjectRoleAlias($project_id, $role_id) {
		return $project_id . ':' . $role_id;
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

	public function projectInit($project) {
		$dir = $this->File->projectDir($project);
		$path = $this->acoAlias . '/' . $dir;
		$node = $this->Acl->Aco->node($path);

		if (!$node) {
			$parent = $this->Acl->Aco->node($this->acoAlias);
			$this->Acl->Aco->create(array('parent_id' => $parent[0]['Aco']['id'], 'model' => null, 'alias' => $dir));
			$this->Acl->Aco->save();
		}

		$this->defaultPermissions($project, $path);
	}

	public function roles() {
		static $roles;

		if (!isset($roles)) {
			$Role = ClassRegistry::init('Role');
			$roles = array();
			foreach ($Role->find('all') as $role) {
				$name = $role['Role']['name'];
				$roles[$name] = $role;
			}
		}

		return $roles;
	}

}