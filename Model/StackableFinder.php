<?php
App::uses('Hash', 'Utility');
App::uses('StackableFinderOptions', 'StackableFinder.Model');

/**
 * StackableFinder class
 *
 * @final Use compotition instead of inheritance.
 */
class StackableFinder {

/**
 * @var Model
 */
	private $model; // @codingStandardsIgnoreLine

/**
 * @var StackableFinderOptions
 */
	private $options; // @codingStandardsIgnoreLine

	private $stack = array(); // @codingStandardsIgnoreLine

/**
 * Constructor
 *
 * This is the internal constructor. Use `do` instead.
 *
 * @param Model $model The model to find
 * @internal
 */
	public function __construct(Model $model) {
		$this->model = $model;
		$this->options = new StackableFinderOptions();
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
			$db = $this->model->getDataSource();
			if ($db instanceof DboSource) {
				return $db->query($name, $args, $this);
			} else {
				throw new BadMethodCallException(sprintf('Datasource %s does not support magic find', get_class($db)));
			}
		} else {
			$callable = array($this->options, $name);
			if (is_callable($callable)) {
				call_user_func_array($callable, $args);
				return $this;
			}
		}
		throw new BadMethodCallException(sprintf('Method %s::%s does not exist', get_class($this), $name));
	}

/**
 * Provides read-only properties
 *
 * @param string $name The name of the property
 * @return bool
 */
	public function __isset($name) {
		switch ($name) {
			case 'alias':
			case 'type':
			case 'value':
				return true;
		}
		return false;
	}

/**
 * Provides read-only properties
 *
 * @param string $name The name of the property
 * @return mixed
 */
	public function __get($name) {
		switch ($name) {
			case 'alias':
				// Hack for DboSource::query()
				return $this->model->alias;
			case 'type':
				// Hack for DboSource::conditionKeysToString()
				return 'expression';
			case 'value':
				// Hack for DboSource::conditionKeysToString()
				return '(' . $this->sql() . ')';
		}

		$class = get_class($this);
		trigger_error("Undefined property: $class::$name", E_USER_NOTICE);
	}

/**
 * Executes finder. The `before` state is executed immediately. 
 * The `after` state is not executed until `done` method is called.
 *
 * @param string $type Type of find operation
 * @param array $options Option fields
 *
 * @return $this
 */
	public function find($type = 'all', $options = array()) {
		$method = '_find' . ucfirst($type);

		$this->options->applyOptions($options);
		$options = $this->model->dispatchMethod($method, array('before', $this->options->getOptions()));
		$this->options->setOptions($options);

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
		$options = $this->options->getOptions();
		$results = $this->model->find('all', $options);
		foreach ($this->stack as $method) {
			$results = $this->model->dispatchMethod($method, array('after', $options, $results));
		}
		return $results;
	}

/**
 * Returns the SQL
 *
 * @return string
 */
	public function sql() {
		$db = $this->model->getDataSource();
		$options = $this->options->getOptions();
		return $db->buildAssociationQuery($this->model, $options);
	}
}
