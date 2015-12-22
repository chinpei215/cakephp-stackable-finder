<?php
require_once App::pluginPath('StackableFinder') . 'Test' . DS . 'bootstrap.php';

App::uses('StackableFinderBehavior', 'StackableFinder.Model/Behavior');
App::uses('StackableFinder', 'StackableFinder.Model');

/**
 * StackableFinderBehavior Test Case
 */
class StackableFinderBehaviorTest extends CakeTestCase {

/**
 * autoFixtures property
 *
 * @var bool
 */
	public $autoFixtures = false;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'core.user',
		'core.article',
		'core.comment',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
	}

/**
 * Tests do
 *
 * @return void
 */
	public function testDo() {
		$Article = ClassRegistry::init('Article');
		$this->assertInstanceOf('StackableFinder', $Article->do());
	}

/**
 * Tests a combination of published and first
 *
 * @return void
 */
	public function testPublishedFirst() {
		$this->loadFixtures('Article');
		$Article = ClassRegistry::init('Article');

		$expected = array(
			'Article' => array(
				'id' => '1',
				'title' => 'First Article',
			)
		);

		$results = $Article
			->do()
				->find('published', array('fields' => array('id', 'title')))
				->find('first')
			->done();

		$this->assertEquals($expected, $results);
	}

/**
 * Tests a combination of published and count
 *
 * @return void
 */
	public function testPublishedCount() {
		$this->loadFixtures('Comment');
		$Comment = ClassRegistry::init('Comment');

		$expected = 5;

		$results = $Comment
			->do()
				->find('published')
				->find('count')
			->done();
		$this->assertEquals($expected, $results);
	}

/**
 * Tests a combination of published and list
 *
 * @return void
 */
	public function testFindPublishedList() {
		$this->loadFixtures('Comment');
		$Comment = ClassRegistry::init('Comment');
		$expected = array(
			1 => 'First Comment for First Article',
			2 => 'Second Comment for First Article',
			3 => 'Third Comment for First Article',
			5 => 'First Comment for Second Article',
			6 => 'Second Comment for Second Article',
		);

		$results = $Comment
			->do()
				->find('published')
				->find('list')
			->done();
		$this->assertEquals($expected, $results);
	}

/**
 * Tests contain
 *
 * @return void
 */
	public function testContain() {
		$this->loadFixtures('Article', 'Comment', 'User');
		$Article = ClassRegistry::init('Article');

		$expected = array(
			'Article' => array(
				'id' => '1',
				'user_id' => '1',
				'title' => 'First Article'
			),
			'User' => array(
				'id' => '1',
				'user' => 'mariano'
			),
			'Comment' => array(
				0 => array(
					'id' => '1',
					'article_id' => '1',
					'comment' => 'First Comment for First Article'
				),
				1 => array(
					'id' => '2',
					'article_id' => '1',
					'comment' => 'Second Comment for First Article'
				),
				2 => array(
					'id' => '3',
					'article_id' => '1',
					'comment' => 'Third Comment for First Article'
				),
				3 => array(
					'id' => '4',
					'article_id' => '1',
					'comment' => 'Fourth Comment for First Article'
				)
			)
		);

		$results = $Article
			->do()
				->find('all', array('contain' => array(
					'User' => array('fields' => array('id', 'user'))
				)))
				->find('all', array('contain' => array(
					'Comment' => array('fields' => array('id', 'article_id', 'comment'))
				)))
				->find('first', array('fields' => array('id', 'user_id', 'title')))
			->done();

		$this->assertEquals($expected, $results);
	}
}
