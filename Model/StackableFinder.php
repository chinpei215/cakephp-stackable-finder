<?php
App::uses('Hash', 'Utility');

/**
 * StackableFinder class
 */
class StackableFinder {

	// @codingStandardsIgnoreStart
	private $Model;
	private $query = array(
		'conditions' => null, 'fields' => null, 'joins' => array(), 'limit' => null,
		'offset' => null, 'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true,
	);
	private $stack = array();
	// @codingStandardsIgnoreEnd

/**
 * Constructor
 *
 * This is the internal constructor. Use `do` instead.
 *
 * @param Model $Model The model to find
 * @internal
 */
	public function __construct(Model $Model) {
		$this->Model = $Model;
	}

/**
 * Handles magic finders and option setting methods
 *
 * @param string $name Name of method to call
 * @param array $args Arguments for the method
 *
 * @return array
 * @throws BadMethodCallException
 */
	public function __call($name, $args) {
		if (preg_match('/^find(\w*)By(.+)/', $name)) {
			// Magic finder
			$db = $this->Model->getDataSource();
			if ($db instanceof DboSource) {
				$this->alias = $this->Model->alias; // Hack for DboSource::query()
				return $db->query($name, $args, $this);
			} else {
				throw new BadMethodCallException(sprintf('Datasource %s does not support magic find', get_class($db)));
			}
		}
		throw new BadMethodCallException(sprintf('Method %s::%s does not exist', get_class($this), $name));
	}

/**
 * Executes finder. The `before` state is executed immediately. 
 * The `after` state is not executed until `done` method is called.
 *
 * @param string $type Type of find operation
 * @param array $query Option fields
 *
 * @return $this
 */
	public function find($type = 'all', $query = array()) {
		$method = '_find' . ucfirst($type);
		if (method_exists($this->Model, $method)) {
			$method = new ReflectionMethod($this->Model, $method);
			$method->setAccessible(true);
		}

		$this->applyOptions($query);

		$this->query = (array)$this->invoke($method, array('before', $this->query));
		$this->stack[] = $method;

		return $this;
	}

/**
 * 3.x compatible. Same as `$finder->find('first')->done()`.
 *
 * @return mixed
 */
	public function count() {
		return $this->find('count')->done();
	}

/**
 * 3.x compatible. Same as `$finder->find('first')->done()`.
 *
 * @return mixed
 */
	public function first() {
		return $this->find('first')->done();
	}

/**
 * Executes stacked finders and returns the results.
 * `Model::find()` and the after states of the finders are executed at this time.
 *
 * @return array
 */
	public function done() {
		$results = $this->Model->find('all', $this->query);
		foreach ($this->stack as $method) {
			$results = $this->invoke($method, array('after', $this->query, $results));
		}
		return $results;
	}

/**
 * Retrieves the current query options
 *
 * @return array
 */
	public function getOptions() {
		return $this->query;
	}

/**
 * Merges or sets query options
 *
 * @param array $options Finder options.
 * @return $this
 */
	public function applyOptions(array $options) {
		$methods = array(
			'fields' => 'select',
			'conditions' => 'where',
			'joins' => 'join',
			'order' => 'order',
			'limit' => 'limit',
			'offset' => 'offset',
			'group' => 'group',
			'contain' => 'contain',
			'page' => 'page',
		);

		foreach ($options as $key => $value) {
			if (isset($methods[$key])) {
				$method = $methods[$key];
				$this->{$method}($value);
			} else {
				$this->setOption($key, $value);
			}
		}

		return $this;
	}

	public function where($conditions) { // @codingStandardsIgnoreLine
		if (isset($this->query['conditions'])) {
			$conditions = array('AND' => array($this->query['conditions'], $conditions));
		}
		return $this->setOption('conditions', $conditions);
	}

	public function contain($associations) { // @codingStandardsIgnoreLine
		return $this->mergeOption('contain', $associations);
	}

	public function join($tables) { // @codingStandardsIgnoreLine
		return $this->mergeOption('joins', $tables);
	}

	public function select($fields) { // @codingStandardsIgnoreLine
		return $this->mergeOption('fields', $fields);
	}

	public function order($fields) { // @codingStandardsIgnoreLine
		return $this->mergeOption('order', $fields);
	}

	public function group($fields) { // @codingStandardsIgnoreLine
		return $this->mergeOption('group', $fields);
	}

	public function limit($num) { // @codingStandardsIgnoreLine
		return $this->setOption('limit', $num);
	}

	public function offset($num) { // @codingStandardsIgnoreLine
		return $this->setOption('offset', $num);
	}

	public function page($num) { // @codingStandardsIgnoreLine
		return $this->setOption('page', $num);
	}

/**
 * Marges a query option into the stack
 *
 * @param array $key The type of the query option
 * @param array $value The value of the query option
 *
 * @return $this
 */
	private function mergeOption($key, $value) { // @codingStandardsIgnoreLine
		if (isset($this->query[$key])) {
			$value = array_merge((array)$this->query[$key], (array)$value);
		}
		return $this->setOption($key, $value);
	}

/**
 * Sets a query option
 *
 * @param array $key The type of the query option
 * @param array $value The value of the query option
 *
 * @return $this
 */
	private function setOption($key, $value) { // @codingStandardsIgnoreLine
		if ($value !== null) {
			$this->query[$key] = $value;
		}
		return $this;
	}

/**
 * Executes given finder
 *
 * @param ReflectionMethod|string $method The name or the reflection of the finder
 * @param array $args Arguments for the finder
 *
 * @return array Query or results.
 */
	private function invoke($method, $args) { // @codingStandardsIgnoreLine
		if ($method instanceof ReflectionMethod) {
			return $method->invokeArgs($this->Model, $args);
		}
		return call_user_func_array(array($this->Model, $method), $args);
	}
}
