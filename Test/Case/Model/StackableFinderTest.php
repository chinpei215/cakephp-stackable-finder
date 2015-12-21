<?php
require_once App::pluginPath('StackableFinder') . 'Test' . DS . 'bootstrap.php';

App::uses('StackableFinder', 'StackableFinder.Model');

/**
 * StackableFinder Test Case
 */
class StackableFinderTest extends CakeTestCase {
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

		$this->StackableFinder = new StackableFinder(ClassRegistry::init('Article'));
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->StackableFinder);

		parent::tearDown();
	}

/**
 * Tests that finders can be stacked
 *
 * @return void
 */
	public function testStackingFinders() {
		$Article = $this->getMockForModel('Article', ['_findX', '_findY', '_findZ', 'find']);

		$finder = new StackableFinder($Article);

		// First
		$query = [
			'conditions' => '1 = 1',
			'fields' => null,
			'joins' => [],
			'limit' => null,
			'offset' => null, 
			'order' => null,
			'page' => 1, 
			'group' => null,
			'callbacks' => true,
		];
		$Article->expects($this->at(0))
			->method('_findX')
			->with('before', $query)
			->will($this->returnArgument(1))
			;

		// Second
		$query = [
			'conditions' => [ // Should be nested
				'AND' => [
					'1 = 1',
					['created >=' => '2001-01-01']
				],
			],
			'fields' => null, 
			'joins' => [], 
			'limit' => 10,
			'offset' => null,
			'order' => [
				'published' => 'asc',
			],
			'page' => 1,
			'group' => null, 
			'callbacks' => true,
		];
		$Article->expects($this->at(1))
			->method('_findY')
			->with('before', $query)
			->will($this->returnArgument(1));

		// Third
		$query = [
			'conditions' => [ // Should be nested deeply
				'AND' => [
					[
						'AND' => [
							'1 = 1',
							['created >=' => '2001-01-01']
						],
					],
					['created >=' => '2002-02-02'],
				],
			],
			'fields' => null, 
			'joins' => [],
			'limit' => 20, // Should be overwritten
			'offset' => null,
			'order' => [ // Should be merged
				'published' => 'asc',
				'id' => 'asc',
			],
			'page' => 1,
			'group' => null, 
			'callbacks' => true,
		];
		$Article->expects($this->at(2))
			->method('_findZ')
			->with('before', $query)
			->will($this->returnArgument(1));

		$results = ['something'];
		$Article->expects($this->at(3))
			->method('find')
			->with('all', $query)
			->will($this->returnValue($results));

		$Article->expects($this->at(4))
			->method('_findX')
			->with('after', $this->isType('array'), $results)
			->will($this->returnValue($results));

		$Article->expects($this->at(5))
			->method('_findY')
			->with('after', $query, $results)
			->will($this->returnValue($results));

		$Article->expects($this->at(6))
			->method('_findZ')
			->with('after', $this->isType('array'), $results)
			->will($this->returnValue($results));

		$finder
			->find('x', ['conditions' => '1 = 1'])
			->find('y', ['conditions' => ['created >=' => '2001-01-01'], 'limit'=>10, 'order' => ['published' => 'asc']])
			->find('z', ['conditions' => ['created >=' => '2002-02-02'], 'limit'=>20, 'order' => ['id' => 'asc']])
			->done();
	}

/**
 * Tests that magic finders also can be stacked
 *
 * @return void
 */
	public function testStackingMagicFinders() {
		$Article = $this->getMockForModel('Article', ['_findAll']);

		$finder = new StackableFinder($Article);

		$query = [
			'conditions' => [
				'Article.published' => 1,
			],
			'fields' => null, 'joins' => [], 'limit' => null, 'offset' => null,
			'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true, 
			'recursive' => null,
		];
		$Article->expects($this->at(0))
			->method('_findAll')
			->with('before', $query)
			->will($this->returnArgument(1));

		$query = [
			'conditions' => [
				'AND' => [
					['Article.published' => 1],
					['Article.user_id' => 2],
				]
			],
			'fields' => null, 'joins' => [], 'limit' => null, 'offset' => null,
			'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true, 
			'recursive' => null,
		];
		$Article->expects($this->at(1))
			->method('_findAll')
			->with('before', $query)
			->will($this->returnArgument(1));

		$finder->findAllByPublished(1)->findAllByUserId(2);
	}

/**
 * Tests that mapped finders also can be stacked
 *
 * @return void
 */
	public function testStackingMappedFinders() {
		$Article = $this->getMockForModel('Article', ['_findAll']);
		$Article->findMethods['published'] = true;

		$finder = new StackableFinder($Article);

		$query = [
			'conditions' => [
				'Article.published' => 1,
			],
			'fields' => null, 'joins' => [], 'limit' => null, 'offset' => null,
			'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true, 
		];
		$Article->expects($this->at(0))
			->method('_findAll')
			->with('before', $query)
			->will($this->returnArgument(1));

		$this->assertFalse(method_exists($Article, '_findPublished'));
		$finder->find('published')->find('all');
	}

/**
 * @expectedException BadMethodCallException
 * @expectedExceptionMessage Method StackableFinder::foo does not exist
 */
	public function testBadMethodCallException() {
		$this->StackableFinder->foo();
	}
}
