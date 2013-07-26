<?php

abstract class CM_QueueAdapter_Abstract {

	/**
	 * @param string $key
	 * @param string $value
	 */
	abstract public function push($key, $value);

	/**
	 * @param string $key
	 * @param string $value
	 * @param int $timestamp
	 */
	abstract public function pushDelayed($key, $value, $timestamp);

	/**
	 * @param string $key
	 * @return string
	 */
	abstract public function pop($key);

	/**
	 * @param string $key
	 * @param int $timestampMax
	 * @return string
	 */
	abstract public function popDelayed($key, $timestampMax);
}
