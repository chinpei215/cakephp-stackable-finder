<?php
App::uses('StackableFinder', 'StackableFinder.Model');

/**
 * StackableFinder behavior
 *
 * Enables a model object to stack finders
 *
 * ### Example:
 *
 * ```
 * $this->Article->q()
 *   ->find('published')
 *   ->find('list')
 *   ->all();
 *
 * ```
 *
 * @final Use compotition instead of inheritance.
 */
class StackableFinderBehavior extends ModelBehavior {

	public $mapMethods = array(
		'/^q$/' => '_query',
		'/^do$/' => '_doStacking', // Deprecated
	);

/**
 * Starts stacking finders. 
 * This is an internal method. Use `q` instead.
 *
 * @param Model $model Model using the behavior
 * @return StackableFinder
 * @internal
 */
	public function _query(Model $model) { // @codingStandardsIgnoreLine
		return new StackableFinder($model);
	}

/**
 * Starts stacking finders.
 *
 * @param Model $model Model using the behavior
 * @deprecated Use `q` instead. This method will be removed in 0.3.0
 * @return StackableFinder
 */
	public function _doStacking(Model $model) { // @codingStandardsIgnoreLine
		return $this->_query($model);
	}
}
