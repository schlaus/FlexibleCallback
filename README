FlexibleCallback
================

[![Build Status](https://img.shields.io/travis/schlaus/FlexibleCallback.svg?style=flat-square)](https://travis-ci.org/schlaus/FlexibleCallback)
[![Coverage Status](https://img.shields.io/coveralls/schlaus/FlexibleCallback/master.svg?style=flat-square)](https://coveralls.io/r/schlaus/FlexibleCallback?branch=master)
[![Latest Version](https://img.shields.io/github/release/schlaus/FlexibleCallback.svg?style=flat-square)](https://packagist.org/packages/schlaus/flexiblecallback)
[![Total Downloads](https://img.shields.io/packagist/dt/schlaus/FlexibleCallback.svg?style=flat-square)](https://packagist.org/packages/schlaus/flexiblecallback)

FlexibleCallback allows for more flexibility in callback functions.

Specifically, it allows you to:
* Unregister callbacks even if it's not otherwise possible
* Change an already registered callback
* Create callback queues
* Simply return values instead of running a function

#### So where would I need something like that?

Well, for example `register_shutdown_function()` won't allow you to unregister or change a callback once it's been registered. FlexibleCallback allows you to circumvent that limitation.

Installation
------------
```
composer require Schlaus/FlexibleCallback
```

...or just download and include `FlexibleCallback.php`. There are no dependencies, so you're good to go.

Usage
-----
#### The basics
```
use Schlaus\FlexibleCallback\FlexibleCallback;

$closure = new FlexibleCallback(function() {
    return "Hello, world!";
});

$closure();     // "Hello, world!"

// register_shutdown_function($closure);

$plainValue = new FlexibleCallback("I'm a return value");

$plainValue();  // "I'm a return value"

$queue = new FlexibleCallback(
    array(
        function() {
            return "Callback 1";
        },
        function() {
            return "Callback 2";
        }
    )
);

$queue();       // "Callback 2"

$queue->unregister();

$queue();       // null
```

#### Change a callback afterwards
```
$callback = new FlexibleCallback("I don't do anything");

$callback->setCallback("I'm much more useful");

$callback();    // "I'm much more useful"

$callback->setCallback(function() {
    return "But I'm the best";
});

$callback();    // "But I'm the best"

// To enforce a callback type there are two handy methods

$callback = new FlexibleCallback;

$callback->setReturnValue("I'm a string")   // Only allows non-callables

$callback->setCallbackFunction(function() { // Only allows callables
    // Awesome things
});

$callback->setCallbackFunction("But I'm not a function!") // InvalidArgumentException
```

#### Callback queues
```
// Create a queue while you're instancing the class...
$queue = new FlexibleCallback(array(
    function() {},
    function() {},
    //...
));
// NOTE: This is a queue, not a stack. First in is first out.

// ... or change an already existing object into a queue
$queue = new FlexibleCallback;
$queue->pushCallbackFunction(function(){});
$queue->pushCallbackFunction(function(){});

// A queue can only contain callables, not values
$queue = new FlexibleCallback("string");
$queue->pushCallbackFunction(function() {
    return "I'm a function";
});

$queue();       // "I'm a function"
```

#### Callback arguments in queues
```
// By default, the same arguments are passed to each callback.
// If a callback in queue returns false, queue execution stops.
// It's possible to change this behaviour so, that instead
// return values are given as the first argument to subsequent
// callbacks.

$queue = new FlexibleCallback;
$queue->includePreviousReturnValue();

$queue = setCallbackFunction(array(
    function($arg1, $arg2) {
        // Since this is the first callback, $arg1 is always null
        // and $arg2 is the first argument passed by the caller
        return false;
    },
    function($arg1, $arg2) {
        // $arg1 is now false, and $arg2 is the same as it was
        // in the previous callback.
    }
));


$queue->excludePreviousReturnValue(); // back to default
```

#### Checking what's been loaded
```
$callback = new FlexibleCallback("Testing!");

$callback->getCallback();   // "Testing!"

$callback->getType();       // "value"

$callback->pushCallbackFunction(function() {
    return "Hello!";
});

$callback->getType();       // "callable"

$function = $callback->getCallback();

$function();                // "Hello!"

$callback->pushCallbackFunction(/*...*/);

$callback->getType();       // "queue"

// $queue will contain all callbacks in queue
$queue = $callback->getCallback();

$callback->setReturnValue(null);

$callback->getType();       // null

$callback->unregister();

$callback->getType();       // null
```

#### Converting the queue to a stack (sort of)
```
$callback->setCallbackFunction(
    array_reverse($callback->getCallback())
);
```

License
-------
[MIT](http://schlaus.mit-license.org)