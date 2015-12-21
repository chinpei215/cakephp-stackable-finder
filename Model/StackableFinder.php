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
 * Handles magic finders.
 *
 * @param string $name Name of method to call
 * @param array $args Arguments for the method
 *
 * @return array
 * @throws BadMethodCallException
 */
	public function __call($name, $args) {
		if (preg_match('/^find(\w+)By(.+)/', $name)) {
			$db = $this->Model->getDataSource();
			if ($db instanceof DboSource) {
				$this->alias = $this->Model->alias; // Hack for DboSource::query()
				return $db->query($name, $args, $this);
			}
		}
		throw new BadMethodCallException(__d('cake_dev', 'Method %1$s::%2$s does not exist', get_class($this), $name));
	}

/**
 * Executes finder. The `before` state is executed immediately. 
 * The `after` state is not executed until `done` method is called.
 *
 * @param string $type Type of find operation
 * @param array $query Option fields
 *
 * @return StackableFinder
 */
	public function find($type = 'all', $query = array()) {
		$method = '_find' . ucfirst($type);
		if (method_exists($this->Model, $method)) {
			$method = new ReflectionMethod($this->Model, $method);
			$method->setAccessible(true);
		}

		foreach ($this->query as $key => $val) {
			if (isset($query[$key])) {
				if ($this->query[$key] === null) {
					$this->query[$key] = $query[$key];
				} else {
					switch ($key) {
						case 'conditions':
							$this->query[$key] = array('AND' => array($this->query[$key], $query[$key]));
							break;
						case 'limit':
						case 'offset':
						case 'page':
							$this->query[$key] = $query[$key];
							break;
						default:
							$this->query[$key] = array_merge((array)$this->query[$key], (array)$query[$key]);
							break;
					}
				}
			}
		}

		$this->query = $this->_invoke($method, array('before', $this->query + $query));
		$this->stack[] = $method;

		return $this;
	}

/**
 * 3.x compatible. Same as `(array)$finder->done()`.
 *
 * @return array
 */
	public function toArray() {
		return (array)$this->done();
	}

/**
 *  3.x compatible. Same as `$finder->find('first')->done()`.
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
			$results = $this->_invoke($method, array('after', $this->query, $results));
		}
		return $results;
	}

/**
 * Executes given finder
 *
 * @param ReflectionMethod|string $method The name or the reflection of the finder
 * @param array $args Arguments for the finder
 *
 * @return array Query or results.
 */
	private function _invoke($method, $args) { // @codingStandardsIgnoreLine
		if ($method instanceof ReflectionMethod) {
			return $method->invokeArgs($this->Model, $args);
		}
		return call_user_func_array(array($this->Model, $method), $args);
	}
}
