<?php 

class LogsController extends AppController {
	public function index() {
		$this->paginate = array(
			'limit' => 20,
			'order' => array(
				'created' => 'DESC',
			)
		);

		$logs = $this->paginate();
		$this->set(compact('logs'));
	}
}