<?php

class CommonAuthComponent extends Component {
	public $components = array(
		'Auth',
	);

	public function isAuthorized($user = null, $args) {
		$args += array(
			'controller' => null,
			'action' => null,
			'pass' => array()
		);

		$request = new CakeRequest(Router::url(array(
			'controller' => $args['controller'],
			'action' => $args['action'],
		) + $args['pass']), false);

		$request->addParams($args);
		return $this->Auth->isAuthorized(null, $request);
	}
}