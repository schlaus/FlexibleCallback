<?php
use Schlaus\FlexibleCallback\FlexibleCallback;

class FlexibleCallbackTest extends PHPUnit_Framework_TestCase
{
	public function testCanInstantiateClass()
	{
		// Class can be instantiated without constructor arguments
		$this->assertInstanceOf('Schlaus\FlexibleCallback\FlexibleCallback', new FlexibleCallback);

		// Class can be instantiated with a closure as a constructor argument
		$this->assertInstanceOf(
			'Schlaus\FlexibleCallback\FlexibleCallback',
			new FlexibleCallback(function () {
				return "I am a callback";
			})
		);

		// Class can be instantiated with a scalar value as constructor argument
		$this->assertInstanceOf('Schlaus\FlexibleCallback\FlexibleCallback', new FlexibleCallback('I am a string'));

		// Class can be instantiated with an array of callbacks
		$this->assertInstanceOf(
			'Schlaus\FlexibleCallback\FlexibleCallback',
			new FlexibleCallback(array(
				function () {
					return "I am a callback";
				},
				function () {
					return "I am also a callback";
				}
			))
		);

		// Class can be instantiated with an array of values
		$this->assertInstanceOf(
			'Schlaus\FlexibleCallback\FlexibleCallback',
			new FlexibleCallback(array(
				1, 2, 3
			))
		);
	}

	public function testWorkingWithClosures()
	{
		$FlexibleCallback = new FlexibleCallback;

		// Loading a callback after instancing the class
		$FlexibleCallback->setCallback(function () {
			return "I am a callback.";
		});

		// Type is reported correctly
		$this->assertEquals("callable", $FlexibleCallback->getType());

		// Calling the callback functions as expected.
		$this->assertEquals("I am a callback.", $FlexibleCallback());

		// Setting callback with a method that only accepts callables
		$FlexibleCallback->setCallbackFunction(function () {
			return "I'm still a callback.";
		});

		// Type is still reported correctly
		$this->assertEquals("callable", $FlexibleCallback->getType());

		// Calling the callback still functions as expected
		$this->assertEquals("I'm still a callback.", $FlexibleCallback());

		$FlexibleCallback->unregister();

		// Type is null after unregistering the callback
		$this->assertNull($FlexibleCallback->getType());

		// Callback also returns null after it has been unregistered
		$this->assertNull($FlexibleCallback());

	}

	public function testWorkingWithScalars()
	{
		$FlexibleCallback = new FlexibleCallback;

		// Setting a string as the return value
		$FlexibleCallback->setCallback("I am a string.");

		// Type is reported correctly
		$this->assertEquals("value", $FlexibleCallback->getType());

		// Calling the callback functions as expected.
		$this->assertEquals("I am a string.", $FlexibleCallback());

		// Setting callback with a method that only accepts values
		$FlexibleCallback->setReturnValue("I'm still a string.");

		// Type is still reported correctly
		$this->assertEquals("value", $FlexibleCallback->getType());

		// Calling the callback still functions as expected
		$this->assertEquals("I'm still a string.", $FlexibleCallback());

		$FlexibleCallback->unregister();

		// Type is null after unregistering the callback
		$this->assertNull($FlexibleCallback->getType());

		// Callback also returns null after it has been unregistered
		$this->assertNull($FlexibleCallback());

		// Setting an int as the return value
		$FlexibleCallback->setCallback(42);

		// Type is reported correctly
		$this->assertEquals("value", $FlexibleCallback->getType());

		// Calling the callback functions as expected.
		$this->assertEquals(42, $FlexibleCallback());

		// Setting callback with a method that only accepts values
		$FlexibleCallback->setReturnValue(123);

		// Type is still reported correctly
		$this->assertEquals("value", $FlexibleCallback->getType());

		// Calling the callback still functions as expected
		$this->assertEquals(123, $FlexibleCallback());

		// Setting a boolean as the return value
		$FlexibleCallback->setCallback(false);

		// Type is reported correctly
		$this->assertEquals("value", $FlexibleCallback->getType());

		// Calling the callback functions as expected.
		$this->assertEquals(false, $FlexibleCallback());

		// Setting callback with a method that only accepts values
		$FlexibleCallback->setReturnValue(true);

		// Type is still reported correctly
		$this->assertEquals("value", $FlexibleCallback->getType());

		// Calling the callback still functions as expected
		$this->assertEquals(true, $FlexibleCallback());

	}

	public function testWorkingWithQueues()
	{
		$FlexibleCallback = new FlexibleCallback;

		$FlexibleCallback->excludePreviousReturnValue();

		$FlexibleCallback->setCallbackFunction(array(
			function() { return false; },
			function() { return true; }
		));

		// If includePreviousReturnValue is false, the queue is interrupted
		// if a callback returns false.
		$this->assertFalse($FlexibleCallback());

		$FlexibleCallback->includePreviousReturnValue();

		// If includePreviousReturnValue is true, returning false does not
		// break execution.
		$this->assertTrue($FlexibleCallback());

		$queue = array(
			function ($arg1, $arg2, $arg3 = true) {
				return $arg3;
			},
			function ($arg1) {
				return $arg1;
			}
		);

		$FlexibleCallback->setCallback($queue);

		// Type is reported correctly
		$this->assertEquals("queue", $FlexibleCallback->getType());

		$FlexibleCallback->excludePreviousReturnValue();

		// Calling the callbacks functions as expected when return value is not included.
		$this->assertEquals("Testing", $FlexibleCallback("Testing", "1, 2, 3"));

		$FlexibleCallback->includePreviousReturnValue();

		// Calling the callbacks functions as expected when return value is included.
		$this->assertEquals("1, 2, 3", $FlexibleCallback("Testing", "1, 2, 3"));

		// Can push another callback to the queue.
		$FlexibleCallback->pushCallbackFunction(function($arg1) {
			return $arg1.", testing.";
		});

		// Type is still reported correctly
		$this->assertEquals("queue", $FlexibleCallback->getType());

		// Calling the callbacks still functions as expected.
		$this->assertEquals("1, 2, 3, testing.", $FlexibleCallback("Testing", "1, 2, 3"));

	}

	public function testChangingCallbackTypes()
	{
		$FlexibleCallback = new FlexibleCallback("I am a string.");

		$FlexibleCallback->setCallbackFunction(function(){});

		// Type is changed to callable since a closure was loaded into the object
		$this->assertEquals("callable", $FlexibleCallback->getType());

		// The closure doesn't return anything, so return value should be null
		$this->assertNull($FlexibleCallback());

		$FlexibleCallback->pushCallbackFunction(function() {
			return true;
		});

		// Type is changed to queue since another closure was pushed into the object
		$this->assertEquals("queue", $FlexibleCallback->getType());

		// We should now be getting (bool) true from the callbacks
		$this->assertTrue($FlexibleCallback());

		$FlexibleCallback->setReturnValue(42);

		// The queue is cleared when the type is changed
		$this->assertEquals("value", $FlexibleCallback->getType());

		// We should only get back the value that was set
		$this->assertEquals(42, $FlexibleCallback());

		$FlexibleCallback->pushCallbackFunction(function(){});

		// Pushing a callback changes type to callable
		$this->assertEquals("callable", $FlexibleCallback->getType());

		$FlexibleCallback->pushCallbackFunction(function(){});

		// Pushing another callback changes type to queue
		$this->assertEquals("queue", $FlexibleCallback->getType());

		$FlexibleCallback->setCallback(null);

		// Setting value to null sets type to null as well
		$this->assertNull($FlexibleCallback->getType());

	}

	public function testOtherCallableTypes()
	{
		$FlexibleCallback = new FlexibleCallback("count");

		// count() is an internal PHP function, it should be callable
		$this->assertEquals("callable", $FlexibleCallback->getType());

		$array = array(42, 6, 7);

		// Count should be 3
		$this->assertEquals(3, $FlexibleCallback($array));

		// Let's make sure date_default_timezone is set to something, because DateTime::createFromFormat wont
		// function correctly without it
		date_default_timezone_set("Europe/Helsinki");

		$FlexibleCallback->setCallback("DateTime::createFromFormat");

		// A static method call should be a valid callable
		$this->assertEquals("callable", $FlexibleCallback->getType());

		// The method call should result in a DateTime object
		$this->assertInstanceOf(
			'DateTime',
			$FlexibleCallback('j-M-Y', '15-Feb-2009')
		);

		$datetime = DateTime::createFromFormat('j-M-Y', '15-Feb-2009');

		$FlexibleCallback->setCallback(array($datetime, "format"));

		// Working with an instanced object should also work
		$this->assertEquals("15-Feb-2009", $FlexibleCallback('j-M-Y'));

	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCantPassScalarAsFunction()
	{
		$FlexibleCallback = new FlexibleCallback;

		$FlexibleCallback->setCallbackFunction("Not really a function.");
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCantFunctionAsScalar()
	{
		$FlexibleCallback = new FlexibleCallback;

		$FlexibleCallback->setReturnValue(function(){});
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCantPushValueToQueue()
	{
		$FlexibleCallback = new FlexibleCallback(array(
			function(){},
			function(){}
		));

		$FlexibleCallback->pushCallbackFunction("Not a callback");
	}

	public function testCanGetAssignedCallback()
	{
		$FlexibleCallback = new FlexibleCallback;

		$FlexibleCallback->setCallbackFunction(function() {
			return 'Hello!';
		});

		$function = $FlexibleCallback->getCallback();

		$this->assertEquals('Hello!', $function());
	}
}
