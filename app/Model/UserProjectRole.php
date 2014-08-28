<?php

class UserProjectRole extends AppModel {
	public $belongsTo = array(
		'User',
		'Role'
	);

	public function roles($user_id, $project_id = null) {

		$conditions = array(
			'user_id' => $user_id,
		);

		if ($project_id) {
			$conditions['project_id'] = $project_id;
		}

		return $this->find('all', array(
			'conditions' => $conditions
		));
	}
}