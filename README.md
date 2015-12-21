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
* Enable `StackableFinder.StackableFinder` behavior in your Model.

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

Note that you cannot stack `find('first')` after `find('list')`:
```php
$articles = $this->Article
	->do()
		->find('list')
		->first('first')
	->done();
```
Probably, you would get an unexpected result. Because `_findFirst` doen't returns the _first_ result actually. That returns the element with index zero.
You can override `_findFirst` in your Model to change this behavior, if necessary.

For 3.x compatibility, you can use `toArray` or `first` or `count` instead of `done`.
```php
$articles = $this->Article
	->do()
		->find('list')
		->toArray();
```
Note that you cannot use `all` because we have no Collection class.
