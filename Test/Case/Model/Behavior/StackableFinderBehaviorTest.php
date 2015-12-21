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
		'plugin.stackable_finder.article',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Article = ClassRegistry::init('Article');
		$this->Article->Behaviors->attach('StackableFinder.StackableFinder');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Article);

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
				'title' => 'Article #1',
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
		$expected = 2;

		$results = $this->Article
			->do()
				->find('published')
				->find('count')
			->done();
		$this->assertEquals($expected, $results);

		$results = $this->Article
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
			'1' => 'Article #1',
			'3' => 'Article #3',
		);

		$results = $this->Article
			->do()
				->find('published')
				->find('list')
			->done();
		$this->assertEquals($expected, $results);

		$results = $this->Article
			->do()
				->find('published')
				->find('list')
				->toArray();
		$this->assertEquals($expected, $results);
	}
}
