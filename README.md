# PHP Quick Profiler

[![Build Status](https://travis-ci.org/jacobemerick/pqp.svg?branch=master)](https://travis-ci.org/jacobemerick/pqp)
[![Code Climate](https://codeclimate.com/github/jacobemerick/pqp/badges/gpa.svg)](https://codeclimate.com/github/jacobemerick/pqp)
[![Test Coverage](https://codeclimate.com/github/jacobemerick/pqp/badges/coverage.svg)](https://codeclimate.com/github/jacobemerick/pqp/coverage)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jacobemerick/pqp/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jacobemerick/pqp/?branch=master)

PHP Quick Profiler is a simple profiler to track application behavior during development.

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install PHP Quick Profiler.

```bash
$ composer require particletree/pqp "^1.0"
```

This will install PHP Quick Profiler. It requires PHP 5.3.0 or newer.

## Usage

The basic profiler only needs a few dedicated lines of code to get up and running. If you want to add in query analysis you'll need to track queries independently and inject them before render.

For the basic profiler:
```php
<?php

$console = new Particletree\Pqp\Console();
$profiler = new Particletree\Pqp\PhpQuickProfiler();
$profiler->setConsole($console);

// let the application do its thing
$console->log('Hello World');
$console->logSpeed();

// after the app shutdown - will spew out HTML
$profiler->display();
```

This will spit out some styled html that sits on the footer of your page with a few messages. The html will only be displayed if `Profiler::display()` is called, so if you want to hide the data (like, in a production environment) then just wrap that call in a check.

### Console

The Console class is a basic holder of logged information. The best way to handle it is to create a new instance and stuff it in a container/service locator and then pass things in throughout your application. Worst case you can throw it in the $_GLOBALS array, but I didn't say that.

Note: the original Particletree release had this class as a psuedo-static singleton. This version does not do that. The Console class follows a more normal, instantiated pattern, and none of its methods should be referenced as static.

#### Console::log()

This logging method will log a string or any other variable into the 'messages' area of the display. Anything that can be handled with a `print_r` can be passed in.

```php
$console = new Particletree\Pqp\Console();
$console->log('A String');

$array = array('a value');
$console->log($array);
```

#### Console::logMemory()

This logging method can either log the current memory usage of the application (for benchmarking) or the memory usage of a given object.

```php
$console = new Particletree\Pqp\Console();
$console->logMemory();

$date = new Datetime('+5 days');
$console->logMemory($date, 'five days from now');
```

#### Console::logError()

This logging method extracts some information from an Exception object for display. You can wire this up with an error handler to track unexpected errors, though the accuracy of the displayed data may be lacking.

```php
$console = new Particletree\Pqp\Console();
try {
  // bad code
} catch (Exception $e) {
  $console->logError($e);
}
```

#### Console::logSpeed()

This logging method takes a snapshot of the current time. The usefulness of this snapshot depends on how well you wire up the profiler start time.

```php
$console = new Particletree\Pqp\Console();
$console->logSpeed('right now');
// some code
$console->logSpeed('a bit later');
```

### PhpQuickProfiler

The PhpQuickProfiler class handles collection of basic system metrics, performs some mapping, and passes data off to a Display object. It also will handle much of the query analysis if you set up things appropriately.

#### PhpQuickProfiler::__construct()

This method returns a new instance of the profiler (no surprises here). The one catch is for timing. If you are interested in tracking load times and accurate speed points you'll need to be aware of when you instantiate this object.

By default, all elapsed times are based on when this object gets constructed. So, you'll want to create an instance of PhpQuickProfiler very early on in your application. There is an alternative if you want to wait for creation - the first parameter of `PhpQuickProfiler::__construct()` can be a microtime double that represents the starting time of the application.

```
$profiler = new Particletree\Pqp\PhpQuickProfiler();
// let the application do its thing

// or
$startTime = microtime(true);
// let the application do its thing
$profiler = new Particletree\Pqp\PhpQuickProfiler($startTime);
```

#### PhpQuickProfiler::setConsole()

This method sets the Console object that contains all pertinent logging information for the application runtime. This is different from the original Particletree, where the profiler 'assumes' that a global static Console object would contain the data.

```
$console = new Particletree\Pqp\Console();
$profiler = new Particletree\Pqp\PhpQuickProfiler();
$profiler->setConsole($console);

// profiler will now pass any logs to Console off to the Display class
$console->log('A string');
```

#### PhpQuickProfiler::setDisplay()

This method sets a customized Display object into the profiler for later invokation. This is different than the original Particletree, which had no concept of a Display object. Because of that, this method is optional. If it is not used, a clean Display object will be used for the display.

```
$profiler = new Particletree\Pqp\PhpQuickProfiler();
$profiler->setDisplay(new Particletree\Pqp\Display());
```

See the Display class for some of the options.

#### PhpQuickProfiler::setProfiledQueries()

This method sends in a list of query profile information for analysis. This information must be passed in an expected format. While this method is optional, it is one of the easier ways to pass in data for query analysis.

```
$profiler = new Particletree\Pqp\PhpQuickProfiler();

$profiledQueries = [
  [
    'sql' => 'SELECT * FROM posts WHERE active = :active',
    'parameters' => ['active' => 1],
    'time' => 5,
  ],
  [
    'sql' => 'UPDATE posts SET active = :active WHERE id = :id',
    'parameters' => ['active' => 1, 'id' => 5],
    'time' => 1,
  ],
];

$profiler->setProfiledQueries($profiledQueries);
```

The format is very important, as any deviation will not be understood. For example, [Aura.Sql](https://github.com/auraphp/Aura.Sql/) includes an optional profiler that returns data in a more modern format. To map to this expected format you must do some manipulation.

```
$profiledQueries = $pdo->getProfiler()->getProfiles();
$profiledQueries = array_filter($profiledQueries, function ($profile) {
  return $profile['function'] == 'perform';
});
$profiledQueries = array_map(function ($profile) {
  return array(
    'sql' => trim(preg_replace('/\s+/', ' ', $profile['statement'])),
    'parameters' => $profile['bind_values'],
    'time' => $profile['duration']
  );
}, $profiledQueries);
```

Any statement that can be handled by prepending an `EXPLAIN` can be passed to this. Also, for this to do anything, you MUST pass in a database connection into the `display` method below.

#### PhpQuickProfiler::display()

This method kicks off the display functionality, which is basically spewing out HTML and styles and such. It also kicks off a lot of the query analysis and metric gathering, which eats up (not much) some memory, so it's only recommended to call this in development or controlled environments. It takes a single parameter, a database connection, that must have a basic PDO-like interface.

```
$profiler = new Particletree\Pqp\PhpQuickProfiler();
$db = new PDO(..);
$profiler->display($db);
```

To sum this up, for query analysis to work, you must pass in this db connection and do one of two things:
- inject the profiledQueries into PhpQuickProfiler::setProfiledQueries() before display is called or
- have a property of `queries` in the database connection that contains an array of profiled queries in the expected format.

The second option is to preserve semi-backwards compatability with the original Particletree release.

### Display

The Display class is usually not interacted with. It manipulates the data from PhpQuickProfiler into a display-friendly format. It does have a few optional construct methods that need to be built upon with future releases.

## Tests

To execute the test suite, you'll need phpunit (and to install package with dev dependencies).

```bash
$ phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Learn More

- [Original Particletree](http://www.particletree.com/features/php-quick-profiler/)

## Credits

- Ryan Campbell (original)
- Kevin Hale (original)
- [Jacob Emerick](https://github.com/jacobemerick) (refactor)

## License

PHP Quick Profiler is licensed under the MIT license. See [License File](LICENSE.md) for more information.
