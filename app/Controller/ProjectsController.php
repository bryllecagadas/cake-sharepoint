<?php

class ProjectsController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->Project->set($this->request->data);
			if ($this->Project->validates()) {
				if ($this->Project->save($this->request->data)) {
					$this->LogHandler->log('Project', 'Project has been modified.', array('project_id' => $this->Project->id));
					$project = $this->Project->findByid($this->Project->id);

					if (!$this->ProjectAcl->projectInit($project)) {
						$this->Session->setFlash('Cannot initialize project.', 'default', array('class' => 'alert alert-danger'));
					}

					$this->redirect(array('action' => 'index'));
				}
			}
		}
	}

	public function add_user($project_id = 0) {
		$project = $this->verify('Project', $project_id);

 		$this->loadModel('User');
 		$this->loadModel('Role');
 		$this->loadModel('UserProjectRole');
	
 		if ($this->request->is('post')) {
 			$user = $this->User->findByid($this->request->data['UserProjectRole']['user_id']);
 			$this->request->data['UserProjectRole']['project_id'] = $project_id;
 			$this->UserProjectRole->set($this->request->data);
 			
			if ($user && $this->UserProjectRole->validates()) {
				$this->UserProjectRole->save($this->request->data);

				$this->LogHandler->log('Project', 'User was added to the project.', array(
					'project_id' => $this->request->data['UserProjectRole']['project_id'], 
					'user_id' => $this->request->data['UserProjectRole']['user_id'],
					'role_id' => $this->request->data['UserProjectRole']['role_id'],
				));
				$this->Session->setFlash('User was added to the group', 'default', array('class' => 'alert alert-success'));
				$this->redirect(array('action' => 'users', $project_id));
			}
 		}
 		
 		$options = array(
 			'fields' => array(
 				'user_id',
			),
 			'conditions' => array(
 				'project_id' => $project_id
 			), 
 			'order' => array(
 				'role_id' => 'ASC'
			)
		);

		$users = array_values($this->UserProjectRole->find('list', $options));

 		$users = $this->User->find('list', array(
 			'fields' => 'username',
 			'conditions' => array(
 				'NOT' => array(
 					'id' => $users,
				)
			)
		));

 		$roles = $this->Role->find('list', array('fields' => 'title'));
		
		$this->set(compact('users', 'project', 'roles'));
	}

	public function ajax_file_upload($project_id = 0) {
		$project_id = null;

		if ($this->request->is('post')) {
			$project_id = $this->request->data['project_id'];
		}
	
		$project = $this->verify('Project', $project_id, true);		
		$this->ProjectAcl->setProject($project);
		$secureId = $this->ProjectAcl->secureProjectId;

		$response = $this->ProjectAcl->uploadFiles($this->request, $this->response);

		echo json_encode($response);
	}

	public function ajax_files() {
		$project_id = null;
		$action = null;

		if ($this->request->is('post')) {
			$project_id = $this->request->data['project_id'];
			$data = $this->request->data;
			if (isset($this->request->data['action'])) {
				$action = $this->request->data['action'];
			}
		}

		$project = $this->verify('Project', $project_id, true);
		$this->ProjectAcl->setProject($project);

		if (!isset($action)) {
			$node_id = $data['node_id'];

			if (isset($data['role'])) {
				$this->ProjectAcl->highlightRole($data['role'], 'disable');
			}
			
			$items = $this->ProjectAcl->projectFiles($node_id);
		} else {
			$items = $this->ProjectAcl->processAction($action, $data);
		}

		echo json_encode($items);
	}

	public function ajax_node_permissions() {
		$project_id = null;

		if ($this->request->is('post')) {
			$project_id = $this->request->data['project_id'];
			$data = $this->request->data;
		}

		$project = $this->verify('Project', $project_id, true);
		$this->ProjectAcl->setProject($project);
		$response = $this->ProjectAcl->checkPermission($data);

		echo json_encode($response);
	}

	public function download($secure_id = '', $token = '') {
		$project = $this->verify('Project', $secure_id, true);
		$user = $this->Auth->user();
		$this->ProjectAcl->setProject($project);
		$file = null;

		$message = 'Token has expired.';

		if ($project && $token) {
			$this->loadModel('Token');
			$file = $this->Token->find('first', array(
				'conditions' => array(
					'token' => $token,
					'user_id' => $user['id'],
					'project_id' => $project['Project']['id'],
				)
			));

			if ($file) {
				$success = $this->ProjectAcl->download($this->response, $file['Token']['path']);
				$this->Token->delete($file['Token']['id']);
				
				if ($success) {
					$this->autoRender = false;
					$this->layout = null;
					return $this->response;
				} else {
					$message = 'File does not exist.';
				}
			}
				
		}

		$this->set(compact('message'));

	}

	public function edit($project_id = 0) {
		$project = $this->verify('Project', $project_id);

		if ($this->request->is('post')) {
			$this->Project->id = $project_id;
			if ($this->Project->save($this->request->data)) {
				$new_name = $this->request->data['Project']['name'];
				if ($new_name != $project['Project']['name']) {
					$this->request->data['Project']['id'] = $project_id;
					$this->ProjectAcl->modifyName($project, $this->request->data);
				}

				$this->LogHandler->log('Project', 'Project was modified.', array('project_id' => $project_id));
				$this->Session->setFlash('Successfully modified project.', 'default', array('class' => 'alert alert-success'));
				$this->redirect(array('action' => 'index'));
			}
		} else {
			$this->request->data = $project;
		}

		$this->set(compact('project'));
	}

	public function edit_user($project_id = 0, $user_id = 0) {
		$project = $this->verify('Project', $project_id);

		$this->loadModel('User');
 		$this->loadModel('Role');
 		$this->loadModel('UserProjectRole');

 		$user_project_role = $this->UserProjectRole->find('first', array(
 			'conditions' => array(
 				'project_id' => $project_id,
 				'user_id' => $user_id,
			)
		));

 		if ($this->request->is('post')) {
 			$this->request->data['UserProjectRole']['project_id'] = $project_id;
 			$this->UserProjectRole->id = $user_project_role['UserProjectRole']['id'];
 			$this->UserProjectRole->set($this->request->data);
			if ($this->UserProjectRole->validates()) {
				$this->UserProjectRole->save($this->request->data);
	
				$this->LogHandler->log('Project', 'User role was modified.', array(
					'project_id' => $project_id,
					'user_id' => $user_id,
					'old_role' => $user_project_role['UserProjectRole']['project_id'],
					'new_role' => $this->request->data['UserProjectRole']['project_id'],
				));
				$this->Session->setFlash('User role was modified.', 'default', array('class' => 'alert alert-success'));
				$this->redirect(array('action' => 'users', $project_id));
			}
 		} else {
 			$this->request->data = $user_project_role;
 		}
 		
 		$users = $this->User->find('list', array('fields' => 'username'));
 		$roles = $this->Role->find('list', array('fields' => 'name'));

 		foreach($roles as &$role) {
 			$role = Inflector::humanize($role);
 		}

		$this->set(compact('users', 'project', 'roles', 'user_project_role'));
	}

	public function files($project_id = 0) {
		$project = $this->verify('Project', $project_id);
		$this->ProjectAcl->setProject($project);
		$secureId = $this->ProjectAcl->secureProjectId;

		$roles = $this->ProjectAcl->roles();
		$has_permission = $this->ProjectAcl->userProjectPermission();
		$user_roles = array_keys($this->ProjectAcl->userProjectRoles());
		$aco_alias = $this->ProjectAcl->acoAlias;

		$this->set(compact('project', 'secureId', 'files', 'roles', 'has_permission', 'aco_alias', 'user_roles'));
	}

	public function index() {
		$options = array();
		$user = $this->Auth->user();
		$add_project = true;

		if (!$user['admin']) {
			$this->loadModel('UserProjectRole');
			$options = array(
				'conditions' => array(
					'id' => array()
				)
			);

			foreach ($this->UserProjectRole->roles($user['id']) as $user_project_role) {
				$options['conditions']['id'][] = $user_project_role['UserProjectRole']['project_id'];
			}

			$add_project = false;
		}

		$projects = array();

		foreach ($this->Project->find('all', $options) as $project) {
			$links = array();
			$this->ProjectAcl->setProject($project);
			foreach (array('edit', 'users') as $action) {
				$args = array(
					'controller' => 'projects',
					'action' => $action,
					'pass' => array($project['Project']['id'])
				);
				$links[$action . '_action'] = $this->CommonAuth->isAuthorized(null, $args);
			}
			$projects[] = $project + $links;
		}

		$this->set(compact('projects', 'add_project'));		
	}

	public function remove_user($project_id, $user_id) {
		$project = $this->verify('Project', $project_id);

		$this->loadModel('UserProjectRole');
		$user_project_role = $this->UserProjectRole->find('first', array(
			'conditions' => array(
				'project_id' => $project_id,
				'user_id' => $user_id,
			)
		));

		if ($user_project_role && $this->UserProjectRole->delete($user_project_role['UserProjectRole']['id'])) {
			$this->LogHandler->log('Project', 'User was removed.', array(
				'project_id' => $project_id,
				'user_id' => $user_id,
			));
			$this->Session->setFlash('Successfully removed the user.', 'default', array('class' => 'alert alert-success'));
		}

		$this->redirect(array(
			'action' => 'users',
			$project_id,
		));
	}

 	public function users($project_id = 0) {
 		$project = $this->verify('Project', $project_id);
 		$this->loadModel('UserProjectRole');

 		$users = array();
 		$options = array(
 			'conditions' => array(
 				'project_id' => $project_id
 			), 
 			'order' => array(
 				'role_id' => 'ASC'
			)
		);

 		foreach ($this->UserProjectRole->find('all', $options) as $user_project_role) {
 			$actions = array();
 			foreach (array('remove_user', 'edit_user') as $action) {
	 			$args = array(
					'controller' => 'projects',
					'action' => $action,
					'pass' => array(
						$user_project_role['UserProjectRole']['project_id'],
						$user_project_role['UserProjectRole']['user_id']
					)
				);

	 			$actions[$action] = $this->CommonAuth->isAuthorized(null, $args);
			}

			$users[] = $user_project_role + $actions;
 		}

		$args = array(
			'controller' => 'projects',
			'action' => 'add_user',
			'pass' => array($project['Project']['id'])
		);

		$add_user = $this->CommonAuth->isAuthorized(null, $args);

 		$this->set(compact('users', 'project', 'add_user'));
	}
}