<?php

class User extends AppModel {
	public $hasMany = array(
		'UserProjectRole'
	);

	public function validates($options = array()) {
		if (
			isset($this->data[$this->alias]['password']) && 
			isset($this->data[$this->alias]['password2'])
		) {
			$password = $this->data[$this->alias]['password'];
			$password2 = $this->data[$this->alias]['password2'];

			if ($password && $password != $password2) {
				$this->invalidate('password', 'Passwords doesn not match');
				$this->invalidate('password2', 'Passwords doesn not match');
			}
		}
		return parent::validates($options);
	}

	public function beforeSave($options = array()) {
		$this->data['User']['password'] = AuthComponent::password($this->data['User']['password']);
		return true;
	}

}