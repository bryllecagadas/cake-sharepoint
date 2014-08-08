<?php

class UsersController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->authAdminOnly('add', 'edit');
		$this->Auth->allow('login', 'logout');
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->User->set($this->request->data);
			if ($this->User->validates()) {
				$this->User->create();
				unset($this->request->data['User']['password2']);
				if ($this->User->save($this->request->data)) {
					$this->Session->setFlash('User has been saved');
					$this->redirect(array('action' => 'index'));
				} else {
					$this->Session->setFlash('There was an issue saving the user.');
				}
			}
		}
	}

	public function edit($user_id = 0) {
		$user = $this->verify('User', $user_id);

		if ($this->request->is('post')) {
			$this->User->set($this->request->data);
			$this->User->id = $user_id;
			if ($this->User->validates()) {
				unset($this->request->data['User']['password2']);
				if ($this->User->save($this->request->data)) {
					$this->Session->setFlash('User has been saved');
					$this->redirect(array('action' => 'index'));
				} else {
					$this->Session->setFlash('There was an issue saving the user.');
				}
			}
		} else {
			unset($user['User']['password']);
			$this->request->data = $user;
		}

		$this->set(compact('user'));
	}

	public function index() {
		$users = $this->User->find('all');
		$this->set(compact('users'));
	}

	public function login() {
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

	public function view() {

	}
}