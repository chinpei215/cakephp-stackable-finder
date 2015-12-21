<?php
class ArticleFixture extends CakeTestFixture {

/**
 * fields property
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'author_id' => array('type' => 'integer', 'null' => true),
		'title' => array('type' => 'string', 'null' => true),
		'body' => 'text',
		'published' => array('type' => 'integer', 'length' => 1),
		'created' => 'datetime',
		'modified' => 'datetime'
	);

/**
 * records property
 *
 * @var array
 */
	public $records = array(
		array('id' => 1, 'author_id' => 1, 'title' => 'Article #1', 'body' => 'Article Body #1', 'published' => 1, 'created' => '2015-01-01 00:00:01', 'modified' => '2015-01-01 00:00:01'),
		array('id' => 2, 'author_id' => 1, 'title' => 'Article #2', 'body' => 'Article Body #2', 'published' => 0, 'created' => '2015-01-01 00:00:02', 'modified' => '2015-01-01 00:00:02'),
		array('id' => 3, 'author_id' => 2, 'title' => 'Article #3', 'body' => 'Article Body #3', 'published' => 1, 'created' => '2015-01-01 00:00:03', 'modified' => '2015-01-01 00:00:03')
	);

}
