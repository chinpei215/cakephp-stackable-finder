<?php
require_once App::pluginPath('StackableFinder') . 'Test' . DS . 'bootstrap.php';

App::uses('StackableFinderBehavior', 'StackableFinder.Model/Behavior');
App::uses('StackableFinder', 'StackableFinder.Model');

/**
 * StackableFinderBehavior Test Case
 */
class StackableFinderBehaviorTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
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

		$this->Article = ClassRegistry::init('Article');
		$this->Comment = ClassRegistry::init('Comment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Article, $this->Comment);

		parent::tearDown();
	}

/**
 * Test do
 *
 * @return void
 */
	public function testDo() {
		$this->assertInstanceOf('StackableFinder', $this->Article->do());
	}

/**
 * Test a combination of published and first
 *
 * @return void
 */
	public function testFindPublishedFirst() {
		$expected = array(
			'Article' => array(
				'id' => 1,
				'title' => 'First Article',
			)
		);

		$results = $this->Article
			->do()
				->find('published', array('fields' => array('id', 'title')))
				->find('first')
			->done();
		$this->assertEquals($expected, $results);

		$results = $this->Article
			->do()
				->find('published', array('fields' => array('id', 'title')))
				->first();
		$this->assertEquals($expected, $results);
	}

/**
 * Test a combination of published and count
 *
 * @return void
 */
	public function testFindPublishedCount() {
		$expected = 5;

		$results = $this->Comment
			->do()
				->find('published')
				->find('count')
			->done();
		$this->assertEquals($expected, $results);

		$results = $this->Comment
			->do()
				->find('published')
				->count();
		$this->assertEquals($expected, $results);
	}

/**
 * Test a combination of published and list
 *
 * @return void
 */
	public function testFindPublishedList() {
		$expected = array(
			1 => 'First Comment for First Article',
			2 => 'Second Comment for First Article',
			3 => 'Third Comment for First Article',
			5 => 'First Comment for Second Article',
			6 => 'Second Comment for Second Article',
		);

		$results = $this->Comment
			->do()
				->find('published')
				->find('list')
			->done();
		$this->assertEquals($expected, $results);

		$results = $this->Comment
			->do()
				->find('published')
				->find('list')
				->toArray();
		$this->assertEquals($expected, $results);
	}
}
