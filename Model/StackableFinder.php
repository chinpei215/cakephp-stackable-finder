<?php
App::uses('Hash', 'Utility');
App::uses('StackableFinderOptions', 'StackableFinder.Model');

/**
 * StackableFinder class
 *
 * @final Use compotition instead of inheritance.
 */
class StackableFinder implements IteratorAggregate {

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
 * This is the internal constructor. Use `q` instead.
 *
 * @param Model $model The model to find
 * @internal
 */
	public function __construct(Model $model) {
		$this->model = $model;
		$this->options = new StackableFinderOptions();
	}

/**
 * Handles magic finders and magic options
 *
 * @param string $name Name of method to call
 * @param array $args Arguments for the method
 *
 * @return array
 * @throws BadMethodCallException
 */
	public function __call($name, $args) {
		// Handle magic finders
		if (preg_match('/^find(\w*)By(.+)/', $name)) {
			$db = $this->model->getDataSource();
			if ($db instanceof DboSource) {
				return $db->query($name, $args, $this);
			} else {
				throw new BadMethodCallException(sprintf('Datasource %s does not support magic find', get_class($db)));
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
				return $this->model->alias; // Hack for DboSource::query()
			case 'type': 
				return 'expression'; // Hack for DboSource::conditionKeysToString()
			case 'value':
				return '(' . $this->sql() . ')'; // Hack for DboSource::conditionKeysToString()
		}

		trigger_error(sprintf("Undefined property: %s::%s", get_class($this), $name), E_USER_NOTICE);
	}

/**
 * Executes query and returns the iterator.
 */
	public function getIterator() {
		return new ArrayIterator($this->toArray());
	}

/**
 * Executes finder. The `before` state is executed immediately. 
 * The `after` state is not executed until `exec` method is called.
 *
 * @param string $type Type of find operation
 * @param array $options Option fields
 *
 * @return $this
 */
	public function find($type = 'all', $options = array()) {
		$method = '_find' . ucfirst($type);

		$this->options->applyOptions($options);
		$options = $this->options->getOptions();
		$options = $this->model->dispatchMethod($method, array('before', $options));
		$this->options->setOptions($options);

		$this->stack[] = $method;

		return $this;
	}

/**
 * Same as `$q->find('first')->exec()`.
 *
 * @return mixed
 */
	public function count() {
		return $this->find('count')->exec();
	}

/**
 * 3.x compatible. Same as `$q->find('first')->exec()`.
 *
 * @return mixed 
 */
	public function first() {
		return $this->find('first')->exec();
	}

/**
 * 3.x compatible. Same as `(array)$q->exec()`.
 *
 * @return mixed
 */
	public function toArray() {
		return (array)$this->exec();
	}

/**
 * Executes stacked finders and returns the results.
 * `Model::find()` and the after states of the finders are executed at this time.
 *
 * @return array
 */
	public function exec() {
		$options = $this->options->getOptions();
		$results = $this->model->find('all', $options);
		foreach ($this->stack as $method) {
			$results = $this->model->dispatchMethod($method, array('after', $options, $results));
		}
		return $results;
	}

/**
 * Executes stacked finders and returns the results.
 * `Model::find()` and the after states of the finders are executed at this time.
 *
 * @return array
 * @deprecated Use `exec` instead. This method will be removed in 0.3.0
 */
	public function done() {
		return $this->exec();
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

/**
 * Delegates StackableFinderOptions::getOptions().
 *
 * @return array
 */
	public function getOptions() {
		return $this->options->getOptions();
	}

/**
 * Delegatets StackableFinderOptions::applyOpiton().
 * Returns $this for fluent interface.
 *
 * @param $name
 * @param $value
 * @return $this For fluen
 * @see StackableFinderOptions::applyOption()
 */
	private function applyOption($name, $value) {
		$this->options->applyOption($name, $value);
		return $this;
	}

/**
 * 3.x compatible.
 *
 * @return $this
 */
/** #@+ */ 
// @codingStandardsIgnoreStart
	public function where($option) {
		return $this->applyOption('conditions', $option);
	}

	public function contain($option) {
		return $this->applyOption('contain', $option);
	}

	public function join($option) {
		return $this->applyOption('joins', $option);
	}

	public function select($option) {
		return $this->applyOption('fields', $option);
	}

	public function order($option) {
		return $this->applyOption('order', $option);
	}

	public function group($option) {
		return $this->applyOption('group', $option);
	}

	public function limit($option) {
		return $this->applyOption('limit', $option);
	}

	public function offset($option) {
		return $this->applyOption('offset', $option);
	}

	public function page($option) {
		return $this->applyOption('page', $option);
	}
// @codingStandardsIgnoreEnd
/** #@- */
}
