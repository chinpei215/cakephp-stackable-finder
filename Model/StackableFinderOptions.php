<?php
App::uses('Hash', 'Utility');

/**
 * StackableFinderOptions class
 *
 * @final Use composition instead of inheritance.
 * @internal This is an back-end class of StackableFinder class.
 */
class StackableFinderOptions {

	private $options = array(  // @codingStandardsIgnoreLine
		'conditions' => null, 
		'fields' => null,
		'joins' => array(),
		'limit' => null,
		'offset' => null,
		'order' => null,
		'page' => 1,
		'group' => null,
		'callbacks' => true,
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
 * @return void
 */
	public function setOptions(array $options) {
		$this->options = $options + $this->options;
	}

/**
 * Merges or sets query options
 *
 * @param array $options Query options.
 * @return void
 */
	public function applyOptions(array $options) {
		foreach ($options as $name => $value) {
			$this->applyOption($name, $value);
		}
	}

/**
 * Merges or sets a single query option
 *
 * @param string $name The name of the query option
 * @param mixed $value The value of the query option
 * @return void
 */
	public function applyOption($name, $value) {
		switch ($name) {
			case 'conditions':
				if (isset($this->options['conditions'])) {
					$value = array('AND' => array($this->options['conditions'], $value));
				}
				return $this->setOption($name, $value);
			case 'fields':
			case 'joins':
			case 'contain':
			case 'order':
			case 'group':
				return $this->setOption($name, $value, true);
			default:
				return $this->setOption($name, $value);
		}
	}

/**
 * Sets a query option
 *
 * @param string $name The name of the query option
 * @param mixed $value The value of the query option
 * @param bool $merge Optional. The value should be merged or not.
 * @return $this
 */
	private function setOption($name, $value, $merge = false) { // @codingStandardsIgnoreLine
		if ($value !== null) {
			if ($merge) {
				$value = (array)$value;
				if (isset($this->options[$name])) {
					$value = array_merge((array)$this->options[$name], $value);
				}
			}
			$this->options[$name] = $value;
		}
	}
}
