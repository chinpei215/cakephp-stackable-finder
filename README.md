[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://img.shields.io/travis/chinpei215/cakephp-stackable-finder/master.svg?style=flat-square)](https://travis-ci.org/chinpei215/cakephp-stackable-finder) 
[![Coverage Status](https://img.shields.io/coveralls/chinpei215/cakephp-stackable-finder.svg?style=flat-square)](https://coveralls.io/r/chinpei215/cakephp-stackable-finder?branch=master) 

# StackableFinder Plugin for CakePHP 2.x

## Requirements

* CakePHP 2.x
* PHP 5.3+

## Installation

* Put `StackableFinder` directory into your plugin directory. You can also install via Composer.
* Enable `StackableFinder` plugin in your `app/Config/bootstrap.php` file.
* Enable `StackableFinder.StackableFinder` behavior in your model.

## Usage

You can start stacking finders by calling `do`:
```php
$articles = $this->Article
	->do()
		->find('all', ['conditions' => ['Article.created >=' => '2015-01-01']])
		->findAllByAuthorId(1) // Magic finder
		->find('published') // Custom finder
		->find('list')
	->done();
```
And by calling `done`, you can execute the query and get the resutls.

For compatibility and convenience, you can use `first` or `count` instead of `done`.
```php
$articles = $this->Article
	->do()
		->find('published')
		->first();
```
This is same as the following:
```php
$articles = $this->Article
	->do()
		->find('published')
		->find('first')
	->done();
```

## Limitation

Note that stacking `first` after `list` doen't work as you expected. Because `_findFirst` doen't returns the _first_ result actually. That returns the element with index `0`.
Also note that stacking `count` after `list` doesn't work. Because `_findCount` expects an array like `[['Model' => ['count' => N ]]]`, but `_findList` changes the array before it called. 
You can override them in your model to change the behaviors, if necessary.
