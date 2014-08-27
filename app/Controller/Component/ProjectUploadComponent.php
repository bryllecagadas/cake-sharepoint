<?php

App::uses('File', 'Utility');
App::uses('Folder', 'Utility');

class ProjectUploadComponent extends Component {
	// CakeRequest
	public $request;

	// CakeResponse
	public $response;

	public function delete() {

	}

	public function get() {
		
	}

	public function get_file_name($name) {
		$raw = preg_replace("/\..+$/", "", $name);
		$ext = str_replace($raw, "", $name);
		$counter = 0;
		while (file_exists($this->options['upload_dir'] . $name)) {
			$name = $raw . $counter . $ext;
			$counter++;
		}

		return $name;
	}

	public function post() {
		$form = $this->request->param('form');
		$files = isset($form['files']) ? $form['files'] : null;

		$info = array();
		if ($files && is_array($files['tmp_name'])) {
			foreach ($files['tmp_name'] as $index => $value) {
				$info[] = $this->upload(
					$files['tmp_name'][$index],
					$files['name'][$index],
					$files['size'][$index],
					$files['type'][$index],
					$files['error'][$index]
				);
			}
		} else {
			$info[] = $this->upload(
				$files['tmp_name'],
				$files['name'],
				$files['size'],
				$files['type'],
				$files['error']
			);
		}

		return array('files' => $info);
	}

	public function process($request, &$response, $options = array()) {
		$this->request = $request;
		$this->response =& $response;
		$this->options = $options;

		$results = array();

		switch (env('REQUEST_METHOD')) {
			case 'GET':
				$results = $this->get();
				break;
			case 'POST':
				$delete = false;

				// We would like to access $_REQUEST['_method'], but since there's no proper way of getting this 
				// in the request object, we do this _hard_ way
				if (!($delete = $this->request->query('_method'))) {
					$delete = $this->request->data('_method');
				}

				if ($delete) {
					$results = $this->delete();
				} else {
					$results = $this->post();
				}
				break;
			case 'DELETE':
				$results = $this->delete();
				break;
			default:
				break;
		}

		return $results;
	}

	public function upload($tmp_name, $name, $size, $type, $error) {
		$file = new stdClass();
		$file->name = $this->get_file_name($name);
		$file->size = intval($size);
		$file->type = $type;

		if (!$error && $file->name) {
			$file_path = $this->options['upload_dir'] . $file->name;
			move_uploaded_file($tmp_name, $file_path);
		} else {
			$file->error = $error;
		}

		return $file;
	}
}