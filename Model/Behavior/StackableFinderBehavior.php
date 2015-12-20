<?php
App::uses('StackableFinder', 'StackableFinder.Model');

class StackableFinderBehavior extends ModelBehavior
{
	public function start(Model $Model) {
		return new StackableFinder($Model);
	}
}
