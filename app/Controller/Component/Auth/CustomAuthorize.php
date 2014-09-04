<?php

App::uses('BaseAuthorize', 'Controller/Component/Auth');

class CustomAuthorize extends BaseAuthorize {

	public $adminOnly = array(
		'projects' => array('add'),
		'users' => array('add', 'index'),
	);

	public function authorize($user, CakeRequest $request) {
		if ($user["admin"]) {
			return true;
		}

		$context = $this->getContext($user, $request);

		if (isset($context['user'])) {
			return $context['user']['User']['id'] == $user['id'];
		} else if (isset($context['project'])) {
			if (in_array($context['action'], array('edit', 'add_user', 'remove_user'))) {
				return 
					in_array('project_manager', $context['roles']) || 
					(isset($context['project_user']) && $context['project_user']['User']['id'] == $user['id']);
			} else if (in_array($context['action'], array('edit_user', 'users'))) {
				return in_array('project_manager', $context['roles']);
			} else {
				return (bool) $context['roles'];
			}
		}

		$controller = $context['controller'];
		$action = $context['action'];

		return isset($this->adminOnly[$controller]) ? !in_array($context['action'], $this->adminOnly[$controller]) : false;
	}

	private function getContext($user, $request) {
		$context = array();
		$UserProjectRole = ClassRegistry::init('UserProjectRole');
		$context['controller'] = $request->params['controller'];
		$context['action'] = $request->params['action'];
		$context['args'] = $request->params['pass'];

		if (!$context['args']) {
			return $context;
		}

		if ($context['controller'] == 'projects' && in_array($context['action'], array('edit', 'view', 'users', 'add_user', 'remove_user', 'edit_user'))) {
			$Project = ClassRegistry::init('Project');
			$UserProjectRole = ClassRegistry::init('UserProjectRole');
			$context['project'] = $Project->findByid($context['args'][0]);
			$context['roles'] = array();
			$options = array(
				'conditions' => array(
					'project_id' => $context['args'][0],
					'user_id' => $user['id'],
				)
			);
			foreach ($UserProjectRole->find('all', $options) as $role) {
				$context['roles'][] = $role['Role']['name'];
			}

			if (isset($context['args'][1]) && $context['action'] == 'remove_user') {
				$User = ClassRegistry::init('User');
				$User->recursive = 0;
				$context['project_user'] = $User->findByid($context['args'][1]);
			}
		} else if ($context['controller'] == 'users' && in_array($context['action'], array('edit'))) {
			$User = ClassRegistry::init('User');
			$context['user'] = $User->findByid($context['args'][0]);
		}

		return $context;
	}

}