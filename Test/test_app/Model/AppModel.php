<?php
/**
 * AppModel for testing
 */
class AppModel extends Model {

/**
 * @var int
 */
	public $recursive = -1;

	public $actsAs = array('StackableFinder.StackableFinder');
}
