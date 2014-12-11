<?php foreach($response['logs'] as &$log) : ?>
<?php $log['message'] = $this->String->format($log['FileHistory']['message'], $log['variables']); ?>
<?php endforeach; ?>
<?php echo json_encode($response); ?>