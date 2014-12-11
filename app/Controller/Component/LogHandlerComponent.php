<?php

class LogHandlerComponent extends Component {

	public $components = array(
		'Auth'
	);

	public $request = null;

	public function log($message, $data = array(), $user = null) {
		if (!$user) {
			$user = $this->Auth->user();
		}

		$this->model->create();
		$this->model->save(array(
			'user_id' => $user['id'],
			'message' => $message,
			'variables' => serialize($data),
			'location' => $this->request->here(),
			'referer' => $this->request->referer(),
		));
	}

	public function prepare(&$log) {
		$variables = unserialize($log['Log']['variables']);
		$replacement = array();

		if (strpos($log['Log']['message'], '!project') !== FALSE) {
			$Project = ClassRegistry::init('Project');
			$Project->recursive = 1;
			if ($project = $Project->findByid($variables['project_id'])) {
				$replacement['project'] = $project['Project']['name'];
			}
		}

		if (strpos($log['Log']['message'], '!author') !== FALSE && isset($log['User'])) {
			$replacement['author'] = $log['User']['username'];
		}

		if (strpos($log['Log']['message'], '!user') !== FALSE) {
			$User = ClassRegistry::init('User');
			if ($user = $User->findByid($variables['user_id'])) {
				$replacement['user'] = $user['User']['username'];
			}
		}

		$Role = ClassRegistry::init('Role');

		if (strpos($log['Log']['message'], '!role') !== FALSE) {
			if ($role = $Role->findByid($variables['role_id'])) {
				$replacement['role'] = $role['Role']['title'];
			}
		}

		if (isset($variables['old_role']) || isset($variables['new_role'])) {
			if (isset($variables['old_role']) && $role = $Role->findByid($variables['old_role'])) {
				$replacement['old_role'] = $role['Role']['title'];
			}

			if (isset($variables['new_role']) && $role = $Role->findByid($variables['new_role'])) {
				$replacement['new_role'] = $role['Role']['title'];
			}
		}

		$log['replacement'] = $replacement;
	}

	public function startup(Controller $controller) {
		$this->request = $controller->request;
		$this->model = ClassRegistry::init('Log');
	}
}