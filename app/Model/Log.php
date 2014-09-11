<?php

class Log extends AppModel {
	public $belongsTo = array(
		'User'
	);

	public function delete_old_entries($value) {
		$date = date('Y-m-d H:i:s', strtotime("-$value days"));
		$conditions = array('Log.created <=' => $date);
		$count = $this->find('count', array('conditions' => $conditions));
		if ($this->deleteAll($conditions)) {
			return $count;
		} else {
			return false;
		}
	}
}