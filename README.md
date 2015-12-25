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

By calling `q()`, You can start stacking finders:
```php
$articles = $this->Article->q()
	->find('all', ['conditions' => ['Article.created >=' => '2015-01-01']])
	->findAllByUserId(1) // Magic finder
	->find('published') // Custom finder
	->find('list')
	->exec();
```
And by calling `exec()`, you can execute the query and get the resutls.

For compatibility and convenience, you can use `first()` or `count()` instead of `exec()`.
```php
$articles = $this->Article->q()
	->find('published')
	->first();
```
This is same as the following:
```php
$articles = $this->Article->q()
	->find('published')
	->find('first')
	->exec();
```

You can also use `where()` or such setters for query options.
```
$articles = $this->Article->q()
	->select(['Article.id', 'Article.title'])
	->contain('Author')
	->where(['Article.user_id' => 1])
	->order('Article.id' => 'DESC')
	->limit(10)
	->offset(0)
	->exec();
```
### Subqueries

You can make subqueries like the following:
```
$q = $this->User->q()->select('id');

$articles = $this->Article->q()
	->where([
		'user_id IN ?' => [$q]
	])
	->exec();
```

You will see that `IN ?` appears after the field name in the left-hand side. 
And you will see also that `$q` appears inside an `[]` in the right-hand side.
It is not compatible with 3.x but it is nessecary at this time.

### Limitation

Note that stacking `find('first')` after `find('list')` doen't work as you expected. Because `_findFirst()` doen't returns the _first_ result actually. That returns the element with index `0`.

Also note that stacking `find('count')` after `find('list')` doesn't work. Because `_findCount()` expects an array like `[['Model' => ['count' => N ]]]`, but `_findList` changes the array before it called. 
You can override them in your model to change the behaviors, if necessary.
