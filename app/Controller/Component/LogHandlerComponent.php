<?php

class LogHandlerComponent extends Component {

	public $components = array(
		'Auth'
	);

	public $request = null;

	public function log($type, $message, $variables = array(), $user = null) {
		if (!$user) {
			$user = $this->Auth->user();
		}

		$this->model->create();
		$this->model->save(array(
			'user_id' => $user['id'],
			'type' => $type,
			'message' => $message,
			'variables' => serialize($variables),
			'location' => $this->request->here(),
			'referer' => $this->request->referer(),
		));
	}

	public function startup($controller) {
		$this->request = $controller->request;
		$this->model = ClassRegistry::init('Log');
	}
}