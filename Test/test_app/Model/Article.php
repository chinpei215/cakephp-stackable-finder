<?php
/**
 * Article for testing
 */
class Article extends AppModel {

/**
 * @var array
 */
	public $actsAs = array('Publishable');

	public $hasMany = array('Comment');

	public $belongsTo = array('User');
}
