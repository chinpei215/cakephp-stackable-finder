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
	public $fixtures = array();

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Article = $this->getMockForModel('Article', array('find', '_findAll', '_findList', '_findFirst'), array('table' => false));
		$this->StackableFinder = new StackableFinder($this->Article);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Article, $this->StackableFinder);

		parent::tearDown();
	}

/**
 * Tests done method
 *
 * @return void
 */
	public function testDone() {
		$expected = array(
			array(
				'Article' => array('id' => 1)
			)
		);

		$this->Article->expects($this->at(0))
			->method('find')
			->with('all')
			->will($this->returnValue($expected));

		$this->assertEquals($expected, $this->StackableFinder->done());
	}

/**
 * Tests that finders can be stacked
 *
 * @return void
 */
	public function testStackingFinders() {
		$Article = $this->Article;
		$finder = $this->StackableFinder;

		// First
		$query = array(
			'conditions' => '1 = 1',
			'fields' => null,
			'joins' => array(),
			'limit' => null,
			'offset' => null,
			'order' => null,
			'page' => 1,
			'group' => null,
			'callbacks' => true,
		);
		$Article->expects($this->at(0))
			->method('_findAll')
			->with('before', $query)
			->will($this->returnArgument(1));

		// Second
		$query = array(
			'conditions' => array( // Should be nested
				'AND' => array(
					'1 = 1',
					array('created >=' => '2001-01-01')
				),
			),
			'fields' => null,
			'joins' => array(),
			'limit' => 10,
			'offset' => null,
			'order' => array(
				'published' => 'asc',
			),
			'page' => 1,
			'group' => null,
			'callbacks' => true,
		);
		$Article->expects($this->at(1))
			->method('_findList')
			->with('before', $query)
			->will($this->returnArgument(1));

		// Third
		$query = array(
			'conditions' => array( // Should be nested deeply
				'AND' => array(
					array(
						'AND' => array(
							'1 = 1',
							array('created >=' => '2001-01-01')
						),
					),
					array('created >=' => '2002-02-02'),
				),
			),
			'fields' => null,
			'joins' => array(),
			'limit' => 20, // Should be overwritten
			'offset' => null,
			'order' => array( // Should be merged
				'published' => 'asc',
				'id' => 'asc',
			),
			'page' => 1,
			'group' => null,
			'callbacks' => true,
		);
		$Article->expects($this->at(2))
			->method('_findFirst')
			->with('before', $query)
			->will($this->returnArgument(1));

		$results = array('something');
		$Article->expects($this->at(3))
			->method('find')
			->with('all', $query)
			->will($this->returnValue($results));

		$Article->expects($this->at(4))
			->method('_findAll')
			->with('after', $this->isType('array'), $results)
			->will($this->returnValue($results));

		$Article->expects($this->at(5))
			->method('_findList')
			->with('after', $query, $results)
			->will($this->returnValue($results));

		$Article->expects($this->at(6))
			->method('_findFirst')
			->with('after', $this->isType('array'), $results)
			->will($this->returnValue($results));

		$finder
			->find('all', array('conditions' => '1 = 1'))
			->find('list', array('conditions' => array('created >=' => '2001-01-01'), 'limit' => 10, 'order' => array('published' => 'asc')))
			->find('first', array('conditions' => array('created >=' => '2002-02-02'), 'limit' => 20, 'order' => array('id' => 'asc')))
			->done();
	}

/**
 * Tests that magic finders also can be stacked
 *
 * @return void
 */
	public function testStackingMagicFinders() {
		$Article = $this->Article;
		$finder = $this->StackableFinder;

		$query = array(
			'conditions' => array(
				'Article.published' => 'Y',
			),
			'fields' => null, 'joins' => array(), 'limit' => null, 'offset' => null,
			'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true,
			'recursive' => null,
		);
		$Article->expects($this->at(0))
			->method('_findAll')
			->with('before', $query)
			->will($this->returnArgument(1));

		$query = array(
			'conditions' => array(
				'AND' => array(
					array('Article.published' => 'Y'),
					array('Article.id' => 2),
				)
			),
			'fields' => null, 'joins' => array(), 'limit' => null, 'offset' => null,
			'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true,
			'recursive' => null,
		);
		$Article->expects($this->at(1))
			->method('_findFirst')
			->with('before', $query)
			->will($this->returnArgument(1));

		$finder->findAllByPublished('Y')->findById(2);
	}

/**
 * Tests that mapped finders also can be stacked
 *
 * @return void
 */
	public function testStackingMappedFinders() {
		$Article = $this->Article;
		$finder = new StackableFinder($Article);

		$query = array(
			'conditions' => array(
				'Article.published' => 'Y',
			),
			'fields' => null, 'joins' => array(), 'limit' => null, 'offset' => null,
			'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true,
		);
		$Article->expects($this->at(0))
			->method('_findAll')
			->with('before', $query)
			->will($this->returnArgument(1));

		$this->assertFalse(method_exists($Article, '_findPublished'));
		$finder->find('published')->find('all');
	}

/**
 * Tests first method
 *
 * @return void
 */
	public function testFirst() {
		$finder = $this->getMock('StackableFinder', array('find', 'done'), array($this->Article));
		$finder->expects($this->at(0))
			->method('find')
			->with('first')
			->will($this->returnValue($finder));

		$finder->expects($this->at(1))
			->method('done')
			->with();

		$finder->first();
	}

/**
 * Tests count method
 *
 * @return void
 */
	public function testCount() {
		$finder = $this->getMock('StackableFinder', array('find', 'done'), array($this->Article));
		$finder->expects($this->at(0))
			->method('find')
			->with('count')
			->will($this->returnValue($finder));

		$finder->expects($this->at(1))
			->method('done')
			->with();

		$finder->count();
	}

/**
 * Tests count method
 *
 * @return void
 */
	public function testToArray() {
		$finder = $this->getMock('StackableFinder', array('find', 'done'), array($this->Article));
		$finder->expects($this->at(0))
			->method('done')
			->with();

		$finder->toArray();
	}

/**
 * Tests that calling an inexistent method throws an exception
 *
 * @expectedException BadMethodCallException
 * @expectedExceptionMessage Method StackableFinder::foo does not exist
 * @return void
 */
	public function testBadMethodCall() {
		$this->StackableFinder->foo();
	}

/**
 * Tests that magic find is unavaiable on other datasources
 *
 * @expectedException BadMethodCallException
 * @expectedExceptionMessage Datasource DataSource does not support magic find
 * @return void
 */
	public function testMagicFindUnavailable() {
		ConnectionManager::create('dummy', array('datasource' => 'DataSource'));
		$this->Article->useDbConfig = 'dummy';
		$this->StackableFinder->findById(1);
	}
}
