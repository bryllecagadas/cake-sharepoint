<?php

class ProjectsController extends AppController {

	public function beforeFilter() {
		$this->auth_admin_only('add');
		parent::beforeFilter();
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->Project->set($this->request->data);
			if ($this->Project->validates()) {
				if ($this->Project->save($this->request->data)) {
					$project = $this->Project->findByid($this->Project->id);

					if (!$this->File->project_init($project)) {
						$this->Session->setFlash('Cannot create project directory.');
					}

					$this->AclExt->project_init($project);
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

	public function edit($project_id = 0) {
		$project = $this->verify('Project', $project_id);
		$this->layout = null;
		$this->autoRender = false;
	}

	public function files($project_id = 0) {
		$project = $this->verify('Project', $project_id);
		$items = $this->File->project_files($project);
	}

	public function files_test($project_id = 0) {
		$project = $this->verify('Project', $project_id);
		$this->File->dir_create($project, 'test');
	}

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