<?php
App::uses('Hash', 'Utility');

class StackableFinder
{
	private $Model;
	private $query = array(
		'conditions' => null, 'fields' => null, 'joins' => array(), 'limit' => null,
		'offset' => null, 'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true,
	);
	private $stack = array();
	private $magicFind = false;

	public function __construct(Model $Model) {
		$this->Model = $Model;
	}

	public function __call($name, $args) {
		if (preg_match('/^find(\w+)By(.+)/', $name)) {
			$db = $this->Model->getDataSource();
			if ($db instanceof DboSource) {
				$this->alias = $this->Model->alias;
				return $db->query($name, $args, $this);
			}
		}
		throw new BadMethodCallException(__d('cake_dev', 'Method %1$s::%2$s does not exist', get_class($this), $name));
	}

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
							$this->query[$key] = array('AND'=>array($this->query[$key], $query[$key]));
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

		$this->query = $this->invoke($method, array('before', $this->query + $query));
		$this->stack[] = $method;

		return $this;
	}

	public function toArray() {
		return (array)$this->done();
	}

	public function count() {
		$this->find('count');
		return $this->done();
	}

	public function first() {
		$this->find('first');
		return $this->done();
	}

	public function done() {
		$results = $this->Model->find('all', $this->query);
		foreach ($this->stack as $method) {
			$results = $this->invoke($method, array('after', $this->query, $results));
		}
		return $results;
	}

	private function invoke($method, $args) {
		if ($method instanceof ReflectionMethod) {
			return $method->invokeArgs($this->Model, $args);
		}
		return call_user_func_array(array($this->Model, $method), $args);
	}
}
