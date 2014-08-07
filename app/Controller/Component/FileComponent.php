<?php

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class FileComponent extends Component {
	public $components = array(
		'AclExt'
	);

	public $project_model = 'Project';

	public function dir_create($project, $dir, $parents = array()) {
		$base = $this->project_dir($project);
		$path = WWW_ROOT . 'files' . DS . $base;
		$aco_path = '';

		foreach ($parents as $parent) {
			$path .= DS . $parent;
			$aco_path .= $parent . '/';
		}

		$path .= DS . $dir;
		$aco_path .= $dir;
		$result = new Folder($path);

		if ($result) {
			$this->AclExt->aco_create($aco_path);
		}

		return $result;
	}

	public function project_dir($project) {
		static $projects;

		$dir = false;
		if ($project && isset($project[$this->project_model])) {
			$project_id = $project[$this->project_model]['id'];
		}

		if (!isset($project_id)) {
			return false;
		}

		if (!isset($projects[$project_id])) {
			$projects[$project_id] = Inflector::slug(strtolower($project[$this->project_model]['name'])) . 
					'_' . $project[$this->project_model]['id'];
		}

		return $projects[$project_id];
	}

	public function project_init($project) {
		$dir = $this->project_dir($project);
		$folder = new Folder();
		$path = WWW_ROOT . 'files' . DS . $dir;
		if (!file_exists($path)) {
			if ($folder->create($path)) {
				return $dir;
			} else {
				return false;
			}
		} else {
			return $dir;
		}
	}

	public function project_files($project) {
		$base = $this->project_dir($project);

	}

	public function user_access($user = null, $path, $action = 'view') {
		if (is_file($path)) {

		}
	}
}