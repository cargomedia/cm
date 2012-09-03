<?php

abstract class CM_SetAdapter_Abstract {

	/**
	 * @param string  $key
	 * @param string  $value
	 */
	abstract public function add($key, $value);

	/**
	 * @param string  $key
	 * @param string  $value
	 */
	abstract public function delete($key, $value);

	/**
	 * @param string $key
	 * @return string[]
	 */
	abstract public function flush($key);

    /**
     * @param string $key
     * @return array
     */
    abstract public function popAll($key);
}
