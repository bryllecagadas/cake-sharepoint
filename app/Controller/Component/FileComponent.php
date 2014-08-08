<?php

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class FileComponent extends Component {
	public $components = array(
		'AclExt',
		'Auth',
	);

	public $projectModel = 'Project';

	public function dirCreate($project, $dir, $parents = array()) {
		$base = $this->projectDir($project);
		$path = WWW_ROOT . $this->AclExt->acoAlias . DS . $base;
		$aco_path = $this->AclExt->acoAlias . '/' . $base . '/';

		foreach ($parents as $parent) {
			$path .= DS . $parent;
			$aco_path .= $parent . '/';
		}

		$path .= DS . $dir;
		$aco_path .= $dir;
		$folder = new Folder();
		$result = file_exists($path) ? TRUE : $folder->create($path);

		if ($result) {
			if ($this->AclExt->acoCreate($aco_path)) {
				$this->AclExt->defaultPermissions($project, $aco_path);
			}
		}

		return $result;
	}

	public function projectDir($project) {
		static $projects;

		$dir = false;
		if ($project && isset($project[$this->projectModel])) {
			$project_id = $project[$this->projectModel]['id'];
		}

		if (!isset($project_id)) {
			return false;
		}

		if (!isset($projects[$project_id])) {
			$projects[$project_id] = Inflector::slug(strtolower($project[$this->projectModel]['name'])) . 
					'_' . $project[$this->projectModel]['id'];
		}

		return $projects[$project_id];
	}

	public function projectInit($project) {
		$dir = $this->projectDir($project);
		$folder = new Folder();
		$path = WWW_ROOT . $this->AclExt->acoAlias . DS . $dir;
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

	public function projectFiles($project) {
		$base = $this->projectDir($project);
		$folder = new Folder($this->AclExt->acoAlias . DS . $base);
		$contents = $this->readRecursive($folder);
		die(pr($contents));
		return $contents;
	}

	public function readRecursive(&$folder, $path = NULL) {
		$contents = array();

		if ($path) {
			$folder->cd($path);
		}

		foreach ($folder->read() as $type => $content) {
			$type = $type ? 'file' : 'folder';
			foreach ($content as $path) {
				if ($type == 'folder') {
					$contents[$path] = $this->readRecursive($folder, $path);
				} else {
					$contents[] = $path;
				}
			}
		}

		$folder->cd('..');
		return $contents;
	}

	public function userAccess($project, $path, $action = 'view') {
		$user = $this->Auth->user($project);
		$base = $this->projectDir($project);
	}
}