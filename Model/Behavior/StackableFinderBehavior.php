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
 * $Model
 *   ->do()
 *     ->find('published')
 *     ->find('list')
 *   ->done();
 *
 * ```
 *
 */
class StackableFinderBehavior extends ModelBehavior {

	public $mapMethods = array(
		'/^do$/' => '_doStacking',
	);

/**
 * Starts stacking finders. 
 * This is an internal method. Use `do` instead.
 *
 * @param Model $Model Model using the behavior
 * @internal
 *
 * @return StackableFinder
 */
	public function _doStacking(Model $Model) { // @codingStandardsIgnoreLine
		return new StackableFinder($Model);
	}
}
