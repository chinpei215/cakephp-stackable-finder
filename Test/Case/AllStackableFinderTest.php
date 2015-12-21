<?php
class AllStackableFinderTest extends PHPUnit_Framework_TestSuite  {

	public static function suite() {
		$suite = new CakeTestSuite('All Tests');
		$suite->addTestDirectoryRecursive(App::pluginPath('StackableFinder') . 'Test' . DS . 'Case' . DS);
		return $suite;
	}

}
