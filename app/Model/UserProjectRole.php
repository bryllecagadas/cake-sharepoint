<?php

class UserProjectRole extends AppModel {
	public $belongsTo = array(
		'User',
		'Role'
	);
}