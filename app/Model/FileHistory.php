<?php

class FileHistory extends AppModel {
  public $belongsTo = array(
    'User'
  );

  public function save_history($action = '', $user_id, $aco_id, $data = array()) {
    $this->create();
    $message = '';

    switch ($action) {
      case 'create':
        $message = 'User !user created the file.';
        break;
      case 'modify':
        $message = 'User !user modified the file.';
        break;
      case 'rename':
        $message = 'File renamed from !old_name to !new_name by !user.';
        break;
      case 'move':
        $message = 'File moved from !old_parent to !new_parent by !user.';
        break;
      case 'copy':
        $message = 'File created as a copy of !old_file by !user.';
        break;
    }

    $history = array(
      'message' => $message,
      'user_id' => $user_id,
      'aco_id' => $aco_id,
    );

    if ($data) {
      $history += array(
        'data' => serialize($data)
      );
    }

    return $this->save($history);
  }
}