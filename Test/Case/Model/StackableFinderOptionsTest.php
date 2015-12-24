<?php
require_once App::pluginPath('StackableFinder') . 'Test' . DS . 'bootstrap.php';

App::uses('StackableFinderOptions', 'StackableFinder.Model');

/**
 * StackableFinderOptions Test Case
 */
class StackableFinderOptionsTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array();

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->StackableFinderOptions = new StackableFinderOptions();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Article, $this->builder);

		parent::tearDown();
	}

/**
 * Tests that applyOptions method calls setters correctly
 *
 * @param string $option The query option name.
 * @param string $method The setter for the query option.
 *
 * @return void
 *
 * @dataProvider dataProviderForTestOptionSetters
 */
	public function testOptionSetters($option, $method) {
		$finder = $this->getMock('StackableFinderOptions', array($method));

		$value = 'something';

		$finder->expects($this->once())
			->method($method)
			->with($value);

		$finder->applyOptions(array($option => $value));
	}

/**
 * Data provider for testOptionSetters
 * 
 * @return array
 */
	public function dataProviderForTestOptionSetters() {
		return array(
			array('fields', 'select'),
			array('conditions', 'where'),
			array('joins', 'join'),
			array('order', 'order'),
			array('limit', 'limit'),
			array('offset', 'offset'),
			array('group', 'group'),
			array('contain', 'contain'),
			array('page', 'page'),
		);
	}

/**
 * Tests each query option stacking correctly
 *
 * @param array $first First query option
 * @param array $second Second query option
 * @param array $expected Expected
 *
 * @return array
 *
 * @dataProvider dataProviderForTestApplyOptions
 */
	public function testApplyOptions($first, $second, $expected) {
		$options = $this->StackableFinderOptions
			->applyOptions($first)
			->applyOptions($second)
			->getOptions();

		$this->assertTrue(Hash::contains($options, $expected));
	}

/**
 * Data provider for testApplyOptions
 *
 * @return array
 */
	public function dataProviderForTestApplyOptions() {
		return array(
			// conditions
			array(
				array('conditions' => array('user_id' => 1)),
				array('conditions' => array('published' => 'Y')),
				array('conditions' => array('AND' => array(array('user_id' => 1), array('published' => 'Y'))))
			),
			// fields
			array(
				array('fields' => array('id')),
				array('fields' => 'title'),
				array('fields' => array('id', 'title'))
			),
			// joins
			array(
				array('joins' => array(
					array(
						'type' => 'INNER',
						'table' => 'users',
						'alias' => 'User',
						'conditions' => 'User.id = Article.user_id',
					),
				)),
				array('joins' => array(
					array(
						'type' => 'LEFT',
						'table' => 'comments',
						'alias' => 'Comment',
						'conditions' => 'Comment.article_id = Article.id',
					),
				)),
				array('joins' => array(
					array(
						'type' => 'INNER',
						'table' => 'users',
						'alias' => 'User',
						'conditions' => 'User.id = Article.user_id',
					),
					array(
						'type' => 'LEFT',
						'table' => 'comments',
						'alias' => 'Comment',
						'conditions' => 'Comment.article_id = Article.id',
					),
				)),
			),
			// limit
			array(
				array('limit' => 10),
				array('limit' => 20),
				array('limit' => 20),
			),
			// offset
			array(
				array('offset' => 10),
				array('offset' => 20),
				array('offset' => 20),
			),
			// order
			array(
				array('order' => 'user_id'),
				array('order' => array('modified' => 'DESC')),
				array('order' => array('user_id', 'modified' => 'DESC')),
			),
			// page
			array(
				array('page' => 1),
				array('page' => 2),
				array('page' => 2),
			),
			// group
			array(
				array('group' => 'user_id'),
				array('group' => 'published'),
				array('group' => array('user_id', 'published')),
			),
			// callbacks
			array(
				array('callbacks' => 'before'),
				array('callbacks' => 'after'),
				array('callbacks' => 'after'),
			),
			// something
			array(
				array('something' => true),
				array('something' => false),
				array('something' => false),
			),
			array(
				array('something' => true),
				array('something' => null),
				array('something' => true),
			),
		);
	}

}
