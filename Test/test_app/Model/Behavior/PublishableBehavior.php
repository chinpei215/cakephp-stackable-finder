<?php
/**
 * 
 */
class PublishableBehavior extends ModelBehavior {

	public $mapMethods = array(
		'/^_findPublished$/' => '_findPublished',
	);

/**
 * A custom finer provided by a behavior class
 *
 * @param Model $Model Model using the behavior
 * @param string $method Mapped method name
 * @param string $state Either "before" or "after"
 * @param array $query Query
 * @param array $results Results
 *
 * @return array
 */
	public function _findPublished(Model $Model, $method, $state, $query, $results = array()) { // @codingStandardsIgnoreLine
		if ($state === 'before') {
			$query['conditions'][$Model->alias . '.published'] = 'Y';
			return $query;
		}
		return $results;
	}
}
