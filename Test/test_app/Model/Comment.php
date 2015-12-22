<?php
/**
 * Comment for testing
 */
class Comment extends AppModel {

	public $actsAs = array('Publishable');

	public $displayField = 'comment';

	public $belongsTo = array('Article', 'Comment');
}
