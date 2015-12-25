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

		$this->Article = $this->getMockForModel('Article', array('find', 'schema', '_findAll', '_findList', '_findFirst'), array('table' => false));
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
 * Tests __isset method
 *
 * @param string $name The name of the property
 * @param mixed $expected Expected results
 * @return void
 *
 * @dataProvider dataProviderForTestMagicIsset
 */
	public function testMagicIsset($name, $expected) {
		$this->assertSame($expected, isset($this->StackableFinder->$name));
	}

/**
 * Data provider for testMagicIsset 
 *
 * @return array
 */
	public function dataProviderForTestMagicIsset() {
		return array(
			array('type', true),
			array('alias', true),
			array('value', true),
			array('undefined', false)
		);
	}

/**
 * Tests __get method
 *
 * @return void
 */
	public function testMagicGet() {
		$finder = $this->StackableFinder;
		$finder->select(array('id'));

		$this->assertEquals('Article', $finder->alias);
		$this->assertEquals('expression', $finder->type);
		$this->assertStringStartsWith('(SELECT id FROM', $finder->value);
		$this->assertNull(@$finder->undefined); // @codingStandardsIgnoreLine
	}

/**
 * Tests getIterator method
 *
 * @return void
 */
	public function testGetIterator() {
		$this->Article->expects($this->once())->method('find');
		$this->assertInstanceOf('Iterator', $this->StackableFinder->getIterator());
	}

/**
 * Tests exec method
 *
 * @return void
 */
	public function testExec() {
		$expected = array(
			array(
				'Article' => array('id' => 1)
			)
		);

		$this->Article->expects($this->at(0))
			->method('find')
			->with('all')
			->will($this->returnValue($expected));

		$this->assertEquals($expected, $this->StackableFinder->exec());
	}

/**
 * Tests done method
 *
 * @return void
 */
	public function testDone() {
		$finder = $this->getMock('StackableFinder', array('exec'), array($this->Article));
		$finder->expects($this->once())
			->method('exec');
		$finder->done();
	}

/**
 * Tests that finders can be stacked
 *
 * @return void
 */
	public function testStackingFinders() {
		$Article = $this->Article;

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
			'conditions' => array(
				'AND' => array(
					'1 = 1',
					array('created >=' => '2001-01-01')
				),
			),
			'fields' => null, 'joins' => array(), 'limit' => null, 'offset' => null,
			'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true,
		);
		$Article->expects($this->at(1))
			->method('_findList')
			->with('before', $query)
			->will($this->returnArgument(1));

		// Third
		$query = array(
			'conditions' => array(
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
			'fields' => null, 'joins' => array(), 'limit' => null, 'offset' => null,
			'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true,
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

		$this->StackableFinder
			->find('all', array('conditions' => '1 = 1'))
			->find('list', array('conditions' => array('created >=' => '2001-01-01')))
			->find('first', array('conditions' => array('created >=' => '2002-02-02')))
			->exec();
	}

/**
 * Tests that magic finders also can be stacked
 *
 * @return void
 */
	public function testStackingMagicFinders() {
		$query = array(
			'conditions' => array(
				'Article.published' => 'Y',
			),
			'fields' => null, 'joins' => array(), 'limit' => null, 'offset' => null,
			'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true,
		);
		$this->Article->expects($this->at(0))
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
		);
		$this->Article->expects($this->at(1))
			->method('_findFirst')
			->with('before', $query)
			->will($this->returnArgument(1));

		$this->StackableFinder->findAllByPublished('Y')->findById(2);
	}

/**
 * Tests that mapped finders also can be stacked
 *
 * @return void
 */
	public function testStackingMappedFinders() {
		$query = array(
			'conditions' => array(
				'Article.published' => 'Y',
			),
			'fields' => null, 'joins' => array(), 'limit' => null, 'offset' => null,
			'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true,
		);
		$this->Article->expects($this->at(0))
			->method('_findAll')
			->with('before', $query)
			->will($this->returnArgument(1));

		$this->assertFalse(method_exists($this->Article, '_findPublished'));
		$this->StackableFinder->find('published')->find('all');
	}

/**
 * Tests first method
 *
 * @return void
 */
	public function testFirst() {
		$finder = $this->getMock('StackableFinder', array('find', 'exec'), array($this->Article));
		$finder->expects($this->at(0))
			->method('find')
			->with('first')
			->will($this->returnValue($finder));

		$finder->expects($this->at(1))
			->method('exec')
			->will($this->returnValue('dummy'));

		$this->assertEquals('dummy', $finder->first());
	}

/**
 * Tests count method
 *
 * @return void
 */
	public function testCount() {
		$finder = $this->getMock('StackableFinder', array('find', 'exec'), array($this->Article));
		$finder->expects($this->at(0))
			->method('find')
			->with('count')
			->will($this->returnValue($finder));

		$finder->expects($this->at(1))
			->method('exec')
			->will($this->returnValue('dummy'));

		$this->assertEquals('dummy', $finder->count());
	}

/**
 * Tests toArray method
 *
 * @return void
 */
	public function testToArray() {
		$finder = $this->getMock('StackableFinder', array('find', 'exec'), array($this->Article));

		$finder->expects($this->at(0))
			->method('exec')
			->will($this->returnValue('dummy'));

		$this->assertEquals(array('dummy'), $finder->toArray());
	}

/**
 * Tests that beforeFind/afterFind events are triggered or not
 *
 * @param mixed $callbacks Type of `callbacks` options
 * @param int $before Expected number of `beforeFind` calls
 * @param int $after Expected number of `afterFind` calls
 *
 * @return void
 *
 * @dataProvider dataProviderForTestEventTriggering
 */
	public function testEventTriggering($callbacks, $before, $after) {
		$db = $this->getMock('DataSource', array('read'));
		$db->expects($this->once())
			->method('read')
			->will($this->returnValue(array()));

		$Article = $this->getMockForModel('Article', array('getDataSource', 'beforeFind', 'afterFind'), array('table' => false));
		$Article->expects($this->any())
			->method('getDataSource')
			->will($this->returnValue($db));

		$Article->expects($this->exactly($before))
			->method('beforeFind');

		$Article->expects($this->exactly($after))
			->method('afterFind');

		$finder = new StackableFinder($Article);
		$finder->find('all', array('callbacks' => $callbacks))->find('all')->exec();
	}

/**
 * Data provider for testEventTriggering
 *
 * @return array
 */
	public function dataProviderForTestEventTriggering() {
		return array(
			array(true, 1, 1),
			array(false, 0, 0),
			array('before', 1, 0),
			array('after', 0, 1),
		);
	}

/**
 * Tests that option handlers work
 *
 * @return void
 */
	public function testOptionHandlers() {
		$options =
			$this->StackableFinder
				->select(array('user_id', 'COUNT(*)'))
				->join(array(
					array(
						'type' => 'INNER',
						'table' => 'users',
						'alias' => 'User',
						'conditions' => 'User.id = Article.user_id',
					),
				))
				->contain('Comment')
				->where(array('published' => 'Y'))
				->group('user_id')
				->order(array('user_id' => 'ASC'))
				->limit(15)
				->offset(0)
				->page(1)
			->getOptions();

		$expected = array(
			'fields' => array('user_id', 'COUNT(*)'),
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
			'callbacks' => true, // Default
		);

		$this->assertEquals($expected, $options);
	}

/**
 * Tests that calling an undefined method throws an exception
 *
 * @expectedException BadMethodCallException
 * @expectedExceptionMessage Method StackableFinder::foo does not exist
 * @return void
 */
	public function testCallingUndefinedMethod() {
		$this->StackableFinder->foo();
	}

/**
 * Tests that getting an undefined peroperty throw an error
 *
 * @expectedException PHPUnit_Framework_Error
 * @return void
 */
	public function testGettingUndefinedProperty() {
		$this->StackableFinder->undefined;
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
