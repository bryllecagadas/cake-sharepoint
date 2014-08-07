<?php

class AclExtComponent extends Component {
	public $components = array(
		'Acl',
		'Auth',
		'File'
	);

	public $aco_alias = 'files';

	public $permission_defaults = array(
		'project_manager' => '*',
		'consultant' => 'read',
		'client' => 'read',
	);

	public function aro_create($aro_alias) {
		$node = $this->Acl->Aro->node($aro_alias);

		if (!$node) {
			$this->Acl->Aro->create();
			$node = $this->Acl->Aro->save(array('alias' => $aro_alias));
		}

		return $node;
	}

	public function aco_create($path) {
	}

	public function initialize(Controller $controller) {
		if (!$this->Acl->Aco->find('first', array('conditions' => array('alias' => 'files')))) {
			$this->Acl->Aco->create(array('parent_id' => null, 'alias' => $this->aco_alias));
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

	public function project_init($project) {
		$dir = $this->File->project_dir($project);
		$project_id = $project[$this->File->project_model]['id'];
		$path = $this->aco_alias . '/' . $dir;
		$parent = $this->Acl->Aco->node($path);

		if (!$parent) {
			$parent = $this->Acl->Aco->node($this->aco_alias);
			$this->Acl->Aco->create(array('parent_id' => $parent[0]['Aco']['id'], 'model' => null, 'alias' => $dir));
			$this->Acl->Aco->save();
		}

		$this->Acl->allow('1', $path);

		foreach ($this->roles() as $name => $role) {
			$aro_alias = $project_id . ':' . $role['Role']['id'];
			$this->aro_create($aro_alias);
			$permission = isset($this->permission_defaults[$name]) ? $this->permission_defaults[$name] : '';

			if ($permission) {
				$this->Acl->allow($aro_alias, $path, $permission);
			}
		}
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