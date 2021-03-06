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
 * Tests query method
 *
 * @return void
 */
	public function testQuery() {
		$Article = ClassRegistry::init('Article');
		$this->assertInstanceOf('StackableFinder', $Article->q());
	}

/**
 * Tests that IteratorAggregate is implemented
 *
 * @return void
 */
	public function testIteratorImplemented() {
		$this->loadFixtures('Article');
		$Article = ClassRegistry::init('Article');

		$q = $Article->q()->select(array('title'));

		$results = iterator_to_array($q);

		$expected = array(
			array('Article' => array('title' => 'First Article')),
			array('Article' => array('title' => 'Second Article')),
			array('Article' => array('title' => 'Third Article')),
		);

		$this->assertSame($expected, $results);
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

		$results = $Article->q()
			->find('published', array('fields' => array('id', 'title')))
			->find('first')
			->exec();

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

		$results = $Comment->q()
			->find('published')
			->find('count')
			->exec();
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

		$results = $Comment->q()
			->find('published')
			->find('list')
			->exec();
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

		$results = $Article->q()
			->find('all', array('contain' => array(
				'User' => array('fields' => array('id', 'user'))
			)))
			->find('all', array('contain' => array(
				'Comment' => array('fields' => array('id', 'article_id', 'comment'))
			)))
			->find('first', array('fields' => array('id', 'user_id', 'title')))
			->exec();

		$this->assertEquals($expected, $results);
	}

/**
 * Tests subquery
 *
 * @return void
 */
	public function testSubQuery() {
		$this->loadFixtures('Article', 'User');
		$User = ClassRegistry::init('User');

		$q = $User->Article->q()->select('user_id');

		$query = $User->q()
			->select(array('id', 'user'))
			->where(array('id NOT IN ?' => array($q)));

		$users = $query->exec();
		$expected = array(
			array('User' => array('id' => 2, 'user' => 'nate')),
			array('User' => array('id' => 4, 'user' => 'garrett')),
		);

		$this->assertEquals($expected, $users);
		$this->assertContains('NOT IN (SELECT', $query->sql());
	}
}
