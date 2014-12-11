<?php

class UsersController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('login', 'logout');
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->User->set($this->request->data);
			if ($this->User->validates()) {
				$this->User->create();
				unset($this->request->data['User']['password2']);
				if ($this->User->save($this->request->data)) {
					$this->LogHandler->log('User !user has been added by !author.', array('user_id' => $this->User->id));
					$this->Session->setFlash('User has been saved', 'default', array('class' => 'alert alert-success'));
					$this->redirect(array('action' => 'index'));
				} else {
					$this->Session->setFlash('There was an issue saving the user.', 'default', array('class' => 'alert alert-danger'));
				}
			}
		}
	}

	public function edit($user_id = 0) {
		$user = $this->verify('User', $user_id);
		$auth_user = $this->Auth->user();

		if ($this->request->is('post')) {
			$this->User->set($this->request->data);
			$this->User->id = $user_id;
			if ($this->User->validates()) {
				unset($this->request->data['User']['password2']);
				
				if (!$auth_user['admin']) {
					unset($this->request->data['User']['admin']);
				}

				if ($this->User->save($this->request->data)) {
					$this->LogHandler->log('User !user has been modified by !author.', array('user_id' => $this->User->id));
					$this->Session->setFlash('User has been saved', 'default', array('class' => 'alert alert-success'));
					$this->redirect(array('action' => 'index'));
				} else {
					$this->Session->setFlash('There was an issue saving the user.', 'default', array('class' => 'alert alert-danger'));
				}
			}
		} else {
			unset($user['User']['password']);
			$this->request->data = $user;
		}

		$current_user = $user['User']['id'] == $auth_user['id'];
		$this->set(compact('user', 'current_user'));
	}

	public function index() {
		$users = $this->User->find('all');
		$this->set(compact('users'));
	}

	public function login() {

		if ($this->Auth->loggedIn()) {
			$this->redirect($this->Auth->redirect());
		}

		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				$this->redirect($this->Auth->redirect());
			} else {
				$this->Auth->flash('Username or password not found.');
			}
		}
	}

	public function logout() {
		$this->redirect($this->Auth->logout());
	}
}