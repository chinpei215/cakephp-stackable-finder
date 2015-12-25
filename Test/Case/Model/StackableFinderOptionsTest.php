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
 *
 * @return void
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
 * Tests applyOption() method
 *
 * @param string $name The name of the option.
 * @param array $values The values to be applied.
 * @param miexed $expected Expected resutls
 * @return void
 *
 * @dataProvider dataProviderForTestApplyOption
 */
	public function testApplyOption($name, $values, $expected) {
		$options = $this->StackableFinderOptions;

		foreach ($values as $value) {
			$options->applyOption($name, $value);
		}

		$results = $options->getOptions();
		$this->assertEquals($expected, $results[$name]);
	}

/**
 * Data provider for testApplyOption()
 *
 * @return array
 */
	public function dataProviderForTestApplyOption() {
		return array(
			// conditions
			array(
				'conditions',
				array(
					array('user_id' => 1),
					array('published' => 'Y'),
				),
				array('AND' => array(array('user_id' => 1), array('published' => 'Y')))
			),
			// fields
			array(
				'fields',
				array('id', 'title'),
				array('id', 'title'),
			),
			// joins
			array(
				'joins',
				array(
					array(
						array(
							'type' => 'INNER',
							'table' => 'users',
							'alias' => 'User',
							'conditions' => 'User.id = Article.user_id',
						),
					),
					array(
						array(
							'type' => 'LEFT',
							'table' => 'comments',
							'alias' => 'Comment',
							'conditions' => 'Comment.article_id = Article.id',
						),
					),
				),
				array(
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
				),
			),
			// contain
			array(
				'contain',
				array('User', 'Comment'),
				array('User', 'Comment'),
			),
			// limit
			array(
				'limit',
				array(10, 20),
				20,
			),
			// offset
			array(
				'offset',
				array(10, 20),
				20,
			),
			// order
			array(
				'order',
				array(
					'user_id',
					array('modified' => 'DESC'),
				),
				array('user_id', 'modified' => 'DESC'),
			),
			// page
			array(
				'page',
				array(1, 2),
				2,
			),
			// group
			array(
				'group',
				array('user_id', 'published'),
				array('user_id', 'published'),
			),
			// callbacks
			array(
				'before',
				array('before', 'after'),
				'after',
			),
			// something
			array(
				'something',
				array(true, false),
				false,
			),
			array(
				'something',
				array(true, null),
				true,
			),
		);
	}

/**
 * Tests each query option stacking correctly
 *
 * @return array
 */
	public function testApplyOptions() {
		$options = $this->StackableFinderOptions;

		// First
		$values = array(
			'fields' => 'user_id',
			'joins' => array(
				array(
					'type' => 'INNER',
					'table' => 'users',
					'alias' => 'User',
					'conditions' => 'User.id = Article.user_id',
				),
			),
			'contain' => 'Comment',
			'conditions' => array('published' => 'Y'),
			'group' => 'user_id',
			'order' => array('user_id' => 'ASC'),
			'limit' => 15,
			'offset' => 0,
			'page' => 1,
			'callbacks' => false,
		);

		$expected = array(
			'fields' => array('user_id'),
			'joins' => array(
				array(
					'type' => 'INNER',
					'table' => 'users',
					'alias' => 'User',
					'conditions' => 'User.id = Article.user_id',
				),
			),
			'contain' => array('Comment'),
			'conditions' => array('published' => 'Y'),
			'group' => array('user_id'),
			'order' => array('user_id' => 'ASC'),
			'limit' => 15,
			'offset' => 0,
			'page' => 1,
			'callbacks' => false,
		);

		$options->applyOptions($values);
		$results = $options->getOptions();
		$this->assertEquals($expected, $results);

		// Second
		$values = array(
			'fields' => 'COUNT(*)',
			'joins' => array(
				array(
					'type' => 'LEFT',
					'table' => 'articles_tags',
					'alias' => 'ArticlesTag',
					'conditions' => 'Article.id = ArticlesTag.article_id',
				),
				array(
					'type' => 'LEFT',
					'table' => 'tags',
					'alias' => 'Tag',
					'conditions' => 'Tag.id = ArticlesTag.tag_id',
				),
			),
			'contain' => null,
			'conditions' => 'Tag.id IS NOT NULL',
			'group' => null,
			'order' => 'COUNT(*)',
			'limit' => 30,
			'offset' => null,
			'page' => null,
			'callbacks' => true,
		);

		$expected = array(
			'fields' => array('user_id', 'COUNT(*)'),
			'joins' => array(
				array(
					'type' => 'INNER',
					'table' => 'users',
					'alias' => 'User',
					'conditions' => 'User.id = Article.user_id',
				),
				array(
					'type' => 'LEFT',
					'table' => 'articles_tags',
					'alias' => 'ArticlesTag',
					'conditions' => 'Article.id = ArticlesTag.article_id',
				),
				array(
					'type' => 'LEFT',
					'table' => 'tags',
					'alias' => 'Tag',
					'conditions' => 'Tag.id = ArticlesTag.tag_id',
				),
			),
			'contain' => array('Comment'),
			'conditions' => array(
				'AND' => array(
					array('published' => 'Y'),
					'Tag.id IS NOT NULL',
				)
			),
			'group' => array('user_id'),
			'order' => array('user_id' => 'ASC', 'COUNT(*)'),
			'limit' => 30,
			'offset' => 0,
			'page' => 1,
			'callbacks' => true,
		);

		$options->applyOptions($values);
		$results = $options->getOptions();
		$this->assertEquals($expected, $results);
	}
}
