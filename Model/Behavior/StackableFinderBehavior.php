<?php
App::uses('StackableFinder', 'StackableFinder.Model');

class StackableFinderBehavior extends ModelBehavior
{
	public $mapMethods = [
		'/^do$/i' => 'doStacking',
	];

	public function doStacking(Model $Model) {
		return new StackableFinder($Model);
	}
}
