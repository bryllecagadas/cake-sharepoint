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
		'Auth' => array(
			'authError' => 'You must log-in to continue',
			'authorize' => array(
				'Custom' => array(
					'adminOnly' => array()
				),
			),
			'loginRedirect' => array(
				'controller' => 'projects',
				'action' => 'index',
			),
		),
		'ProjectAcl',
		'RequestHandler',
		'Session',
	);

	public $helpers = array(
		'Session',
		'Html' => array('className' => 'BoostCake.BoostCakeHtml'),
		'Form' => array('className' => 'BoostCake.BoostCakeForm'),
		'Paginator' => array('className' => 'BoostCake.BoostCakePaginator'),
	);

	public $layout = 'BoostCake.bootstrap3';

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

	public function beforeFilter() {
		parent::beforeFilter();
		if (isset($this->request->params['prefix']) && $this->request->params['prefix'] == 'ajax') {
			$this->RequestHandler->ext = 'json';
			$this->layout = null;
			$this->autoRender = false;
		}
		$this->set(array('auth_user' => $this->Auth->user()));
	}

	public function verify($model, $id, $hashed = false) {
		if (!isset($this->{$model})) {
			$this->loadModel($model);
		}

		$notFound = true;

		if ($id) {
			if (!$hashed) {
				$item = $this->{$model}->findByid($id);
			} else {
				$salt = Configure::read('Security.salt');
				$item = $this->{$model}->find('first', array(
					'conditions' => array(
						"SHA1(CONCAT('$salt', {$model}.id))" => $id,
					)
				));
			}
		}

		if (!empty($item)) {
			$notFound = false;
		}

		if ($notFound) {
			$this->Session->setFlash($model . ' was not found');
			$this->redirect(array('action' => 'index'));
		}

		return $item;
	}
}