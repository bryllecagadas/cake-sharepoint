<?php

class ProjectsController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		$this->authAdminOnly('add');
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->Project->set($this->request->data);
			if ($this->Project->validates()) {
				if ($this->Project->save($this->request->data)) {
					$project = $this->Project->findByid($this->Project->id);

					if (!$this->ProjectAcl->projectInit($project)) {
						$this->Session->setFlash('Cannot initialize project.');
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
 			$this->request->data['UserProjectRole']['project_id'] = $project_id;
 			$this->UserProjectRole->set($this->request->data);
			if ($this->UserProjectRole->validates()) {
				$this->UserProjectRole->save($this->request->data);
	
				$this->Session->setFlash('User was added to the group');
				$this->redirect(array('action' => 'users', $project_id));
			}
 		}
 		
 		$users = $this->User->find('list', array('fields' => 'username'));
 		$roles = $this->Role->find('list', array('fields' => 'name'));
		$this->set(compact('users', 'project', 'roles'));
	}

	public function ajax_file_upload($project_id = 0) {
		$project_id = null;
		$this->layout = null;
		$this->autoRender = false;

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
		$this->layout = null;
		$this->autoRender = false;

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
		$this->layout = null;
		$this->autoRender = false;
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
		$this->layout = null;
		$this->autoRender = false;
	}

	public function files($project_id = 0) {
		$project = $this->verify('Project', $project_id);
		$this->ProjectAcl->setProject($project);
		$secureId = $this->ProjectAcl->secureProjectId;

		$roles = $this->ProjectAcl->roles();
		$has_permission = $this->ProjectAcl->userProjectPermission();
		$aco_alias = $this->ProjectAcl->acoAlias;

		$this->set(compact('project', 'secureId', 'files', 'roles', 'has_permission', 'aco_alias'));
	}

	// public function files_test($project_id = 0) {
	// 	$project = $this->verify('Project', $project_id);
	// 	$this->ProjectAcl->setProject($project);
	// 	$this->ProjectAcl->dirCreate('test');
	// }

	public function index() {
		$projects = $this->Project->find('all');
		$this->set(compact('projects'));		
	}

 	public function users($project_id = 0) {
 		$project = $this->verify('Project', $project_id);
 		$this->loadModel('UserProjectRole');

 		$users = $this->UserProjectRole->find('all', array(
 			'conditions' => array(
 				'project_id' => $project_id
 			), 
 			'order' => array(
 				'role_id' => 'ASC'
			)
		));

 		$this->set(compact('users', 'project'));
	}

	public function view() {
		
	}
}