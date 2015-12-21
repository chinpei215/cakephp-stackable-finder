<?php
App::build(array(
	'Model' => array(App::pluginPath('StackableFinder') . 'Test' . DS . 'test_app' . DS . 'Model' . DS),
	'Model/Behavior' => array(App::pluginPath('StackableFinder') . 'Test' . DS . 'test_app' . DS . 'Model' . DS . 'Behavior' . DS),
), true);

