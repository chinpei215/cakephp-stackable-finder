<?php
class PublishableBehavior extends ModelBehavior
{
	public $mapMethods = array(
		'/^_findPublished$/i' => '_findPublished',
	);

	public function _findPublished(Model $Model, $method, $state, $query, $results = array()) {
		if ($state === 'before') {
			$query['conditions'][$Model->alias . '.published'] = 1;
			return $query;
		}
		return $results;
	}
}
