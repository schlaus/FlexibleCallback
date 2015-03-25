<?php

namespace Schlaus\FlexibleCallback;

/**
 * FlexibleCallback allows for more flexibility in callback functions.
 *
 * Inspired by http://stackoverflow.com/a/2726538/1697755
 *
 * Class FlexibleCallback
 * @package   Schlaus\FlexibleCallback
 * @copyright 2015 Klaus Karkia
 * @license   http://schlaus.mit-license.org  MIT License
 */
class FlexibleCallback
{
	/**
	 * Holds the callback(s)
	 * @var null
	 */
	protected $callback = null;
	/**
	 * Type of current callback(s)
	 * @var null
	 */
	protected $type = null;

	/**
	 * Should return values be passed along a queue
	 * @var bool
	 */
	protected $includeReturnValue = false;

	/**
	 * Constructor.
	 *
	 * @param mixed $callback The callback to be set.
	 */
	public function __construct($callback = null)
	{
		if (!is_null($callback)) {
			$this->setCallback($callback);
		}
	}

	/**
	 * Magic method that allows the object to be called like a function.
	 *
	 * @return mixed|null
	 */
	public function __invoke()
	{
		$return = null;

		switch($this->type) {
			case "value":
				$return = $this->callback;
				break;
			case "callable":
				$return = call_user_func_array($this->callback, func_get_args());
				break;
			case "queue":
				$originalArgs = func_get_args();
				foreach ($this->callback as $callback) {
					$args = $originalArgs;
					if ($this->includeReturnValue === true) {
						array_unshift($args, $return);
					}
					$return = call_user_func_array($callback, $args);
					if ($this->includeReturnValue === false && $return === false) {
						break;
					}
				}
				break;
		}

		return $return;
	}

	/**
	 * Enable passing return values along the queue
	 */
	public function includePreviousReturnValue()
	{
		$this->includeReturnValue = true;
	}

	/**
	 * Disable passing return values along the queue
	 */
	public function excludePreviousReturnValue()
	{
		$this->includeReturnValue = false;
	}

	/**
	 * Set a callback function. This method only accepts arguments that are callable.
	 *
	 * @param callable $callback
	 */
	public function setCallbackFunction($callback)
	{
		if (!is_callable($callback)) {
			if (is_array($callback)) {
				$this->setCallback(null);
				foreach($callback as $item) {
					if (is_callable($item)) {
						$this->pushCallbackFunction($item);
					} else {
						throw new \InvalidArgumentException(
							__CLASS__.'::setCallback($callback) - $callback must be a valid callback.'
						);
					}
				}
				$this->type = "queue";
			} else {
				throw new \InvalidArgumentException(
					__CLASS__.'::setCallback($callback) - $callback must be a valid callback.'
				);
			}
		} else {
			$this->type = "callable";
			$this->callback = $callback;
		}
	}

	/**
	 * Push a new callback to the queue. This method only accepts arguments that are callable.
	 *
	 * @param callable $callback
	 *
	 * @throws \InvalidArgumentException If something else than a valid callable is passed.
	 */
	public function pushCallbackFunction($callback)
	{
		if (is_callable($callback)) {
			if ($this->type === "callable") {
				$this->type = "queue";
				$this->callback = array($this->callback);
				$this->callback[] = $callback;
			} elseif ($this->type === "value") {
				$this->type = "callable";
				$this->callback = $callback;
			} else {
				$this->callback[] = $callback;
			}
		} else {
			throw new \InvalidArgumentException(
				__CLASS__.'::pushCallbackFunction($callback) - $callback must be a valid callback.'
			);
		}
	}

	/**
	 * Set a value to be returned when the callback is called, instead of calling an actual function.
	 *
	 * @param mixed $value
	 *
	 * @throws \InvalidArgumentException If a callable is passed.
	 */
	public function setReturnValue($value)
	{
		if (is_callable($value)) {
			throw new \InvalidArgumentException(
				__CLASS__.'::setReturnValue($value) - $value can\'t be callable.'
			);
		}
		$this->type = "value";
		$this->callback = $value;
	}

	/**
	 * Set a callback of any type.
	 *
	 * @param mixed|null $callback
	 */
	public function setCallback($callback)
	{
		$this->type = "value";
		if (is_callable($callback)) {
			$this->type = "callable";
		} elseif (is_array($callback)) {
			$queue = true;
			foreach ($callback as $item) {
				if (!is_callable($item)) {
					$queue = false;
					break;
				}
			}
			if ($queue) {
				$this->type = "queue";
			}
		} elseif (is_null($callback)) {
			$this->type = null;
		}
		$this->callback = $callback;
	}

	/**
	 * Get the current callback.
	 *
	 * @return mixed|null
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * Get the type of the current callback.
	 *
	 * Possible values: 'value', 'callable', 'queue', null
	 *
	 * @return string|null
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Unregister callbacks.
	 */
	public function unregister()
	{
		$this->callback = null;
		$this->type     = null;
	}
}