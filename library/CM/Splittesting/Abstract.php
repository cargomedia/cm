<?php

abstract class CM_Splittesting_Abstract {

	/**
	 * @return string
	 */
	abstract public function getJs();

	/**
	 * @return string
	 */
	 abstract public function getHtml();

	/**
	 * @return boolean
	 */
	public function enabled() {
		return (boolean) Config::get()->splittesting;
	}
}
