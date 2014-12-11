<?php

App::uses('File', 'Utility');
App::uses('Folder', 'Utility');

class ProjectUploadComponent extends Component {
	public $request;

	public $response;

	public $passPhrase;

	public function delete() {

	}

	public function encode($file_path) {
		$temp_path = tempnam(dirname($file_path), 'TMP');
		$temp = fopen($temp_path, 'w');

		$resource = fopen($file_path, 'r');
		$cipher = Configure::read('OpenSSL.cipher');
		$iv = Configure::read('OpenSSL.iv');

		if ($iv && $cipher) {
			while(($data = fgets($resource))) {
				fputs($temp, openssl_encrypt($data, $cipher, $this->passPhrase, 0, $iv) . PHP_EOL);
			}
			fclose($temp);
			fclose($resource);
			rename($temp_path, $file_path);
		}
	}

	public function get() {
		
	}

	public function get_file_name($name) {
		if (
			isset($this->request->data['overwrite_method']) && 
			$this->request->data['overwrite_method'] == 'overwrite'
		) {
			return $name;
		}
		
		$raw = preg_replace("/\..+$/", "", $name);
		$ext = str_replace($raw, "", $name);
		$counter = 1;
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

		if (isset($this->request->data['overwrite_method'])) {
			$file->overwrite_method = $this->request->data['overwrite_method'];
		}

		if (!$error && $file->name) {
			$file_path = $this->options['upload_dir'] . $file->name;
			move_uploaded_file($tmp_name, $file_path);
			
			if ($this->passPhrase) {
				$this->encode($file_path);
			}
		} else {
			$file->error = $error;
		}

		return $file;
	}
}
