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
 * Tests getOptions() method.
 */
	public function testGetOptions() {
		$options = $this->StackableFinderOptions->getOptions();
		$expected = array(
			'conditions' => null, 
			'fields' => null, 
			'joins' => array(),
			'limit' => null,
			'offset' => null, 
			'order' => null,
			'page' => 1, 
			'group' => null,
			'callbacks' => true,
		);
		$this->assertEquals($expected, $options);
	}

/**
 * Tests each query option stacking correctly
 *
 * @param array $values Query options
 * @param array $expected Expected
 *
 * @return array
 *
 * @dataProvider dataProviderForTestApplyOptions
 */
	public function testApplyOptions($values, $expected) {
		$options = $this->StackableFinderOptions;

		foreach ($values as $value) {
			$options->applyOptions($value);
		}
		$this->assertTrue(Hash::contains($options->getOptions(), $expected));
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
				array( 
					array('conditions' => array('user_id' => 1)),
					array('conditions' => array('published' => 'Y')) 
				),
				array('conditions' => array('AND' => array(array('user_id' => 1), array('published' => 'Y'))))
			),
			// fields
			array(
				array(
					array('fields' => array('id')),
					array('fields' => 'title'),
				),
				array('fields' => array('id', 'title'))
			),
			// joins
			array(
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
				),
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
				array(
					array('limit' => 10),
					array('limit' => 20),
				),
				array('limit' => 20),
			),
			// offset
			array(
				array(
					array('offset' => 10),
					array('offset' => 20),
				),
				array('offset' => 20),
			),
			// order
			array(
				array(
					array('order' => 'user_id'),
					array('order' => array('modified' => 'DESC')),
				),
				array('order' => array('user_id', 'modified' => 'DESC')),
			),
			// page
			array(
				array(
					array('page' => 1),
					array('page' => 2),
				),
				array('page' => 2),
			),
			// group
			array(
				array(
					array('group' => 'user_id'),
				array('group' => 'published'),
				),
				array('group' => array('user_id', 'published')),
			),
			// callbacks
			array(
				array(
					array('callbacks' => 'before'),
					array('callbacks' => 'after'),
				),
				array('callbacks' => 'after'),
			),
			// something
			array(
				array(
					array('something' => true),
					array('something' => false),
				),
				array('something' => false),
			),
			array(
				array(
					array('something' => true),
					array('something' => null),
				),
				array('something' => true),
			),
		);
	}
}
