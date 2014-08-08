<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $components = array(
		'Acl',
		'AclExt',
		'Auth' => array(
			'loginRedirect' => array(
				'controller' => 'projects',
				'action' => 'index',
			),
			'authorize' => array(
				'Custom' => array(
					'adminOnly' => array()
				),
			),
			'authError' => 'You must log-in to continue',
		),
		'File',
		'Session',
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function verify($model, $id) {
		if (!isset($this->{$model})) {
			$this->loadModel($model);
		}

		$item = $this->{$model}->findByid($id);
		if (!$item) {
			$this->Session->setFlash($model . ' was not found');
			$this->redirect(array('action' => 'index'));
		}

		return $item;
	}

	protected function authAdminOnly() {
		$args = func_get_args();
		if (is_array($args[0])) {
			$actions = $args[0];
		} else {
			$actions = $args;
		}
		
		foreach ($actions as $action) {
			$this->Auth->authorize['Custom']['adminOnly'][] = $action;
		}
	}
}