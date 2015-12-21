<?php
/**
 * All StackableFinder plugin tests
 */
class AllStackableFinderTest extends PHPUnit_Framework_TestSuite {

/**
 * Assemble Test Suite
 * 
 * @return PHPUnit_Framework_TestSuite
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Tests');
		$suite->addTestDirectoryRecursive(App::pluginPath('StackableFinder') . 'Test' . DS . 'Case' . DS);
		return $suite;
	}

}
