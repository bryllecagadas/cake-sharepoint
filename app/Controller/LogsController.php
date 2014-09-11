<?php 

class LogsController extends AppController {
	private $log_options = array(
		'delete-3' => 'Delete log entries older than 3 days',
		'delete-7' => 'Delete log entries older than 1 week',
		'delete-30' => 'Delete log entries older than 1 month',
	);

	public function index() {
		$this->paginate = array(
			'limit' => 20,
			'order' => array(
				'created' => 'DESC',
			)
		);

		$logs = $this->paginate();
		$options = $this->log_options;

		$this->set(compact('logs', 'options'));
	}

	public function process() {
		$redirect = true;
		$flash_message = 'Action was invalid.';
		$flash_message_class = 'alert-danger';
		if ($this->request->is('post')) {
			$process = $this->request->data('process');
			$proceed = $this->request->data('proceed');
			if ($process) {
				$parts = explode('-', $process);
				$action = $parts[0];
				$value = $parts[1];
				$process_message = $this->log_options[$process];
				$message = 'Are you sure you want to ' . strtolower($process_message) . '?';
				$redirect = false;
				$this->set(compact('action', 'message', 'process'));
				if ($proceed) {
					if (($count = $this->Log->delete_old_entries($value))) {
						$redirect = true;
						$flash_message = "$count items processed: " . $process_message;
						$flash_message_class = 'alert-success';
					} else {
						$flash_message = 'There was an error with processing: ' . $process_message;
					}
				}
			}
		}

		if ($redirect) {
			$this->Session->setFlash($flash_message, 'default', array('class' => 'alert ' . $flash_message_class));
			$this->redirect(array('action' => 'index'));
		}
	}

}