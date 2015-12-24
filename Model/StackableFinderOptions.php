<?php
App::uses('Hash', 'Utility');

/**
 * StackableFinderOptions class
 *
 * @final Use compotition instead of inheritance.
 * @internal
 */
class StackableFinderOptions {

	private $options = array(  // @codingStandardsIgnoreLine
		'conditions' => null, 'fields' => null, 'joins' => array(), 'limit' => null,
		'offset' => null, 'order' => null, 'page' => 1, 'group' => null, 'callbacks' => true,
	);

/**
 * Retrieves the current query options
 *
 * @return array
 */
	public function getOptions() {
		return $this->options;
	}

/**
 * Overwrites the all current query options.
 *
 * @param array $options Query options.
 *
 * @return $this
 */
	public function setOptions(array $options) {
		$this->options = $options;
		return $this;
	}

/**
 * Merges or sets query options
 *
 * @param array $options Query options.
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
		if (isset($this->options['conditions'])) {
			$conditions = array('AND' => array($this->options['conditions'], $conditions));
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
		if (isset($this->options[$key])) {
			$value = array_merge((array)$this->options[$key], (array)$value);
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
			$this->options[$key] = $value;
		}
		return $this;
	}
}
