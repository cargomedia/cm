<?php

class CM_CssLegacy {
	/**
	 * @var CM_CssAdapter_Abstract
	 */
	private $_adapter;

	/**
	 * @param string		  $css
	 * @param CM_Render	   $render
	 * @param CM_CssLegacy|null	 $presets
	 * @param string|null	 $prefix
	 */
	public function __construct($css, CM_Render $render, CM_CssLegacy $presets = null, $prefix = null) {
		$this->_adapter = new CM_CssAdapter_CM($css, $render, $presets, $prefix);
	}

	/**
	 * @return array
	 */
	public function getRules() {
		return $this->_adapter->getRules();
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$output = '';
		foreach ($this->getRules() as $selector => $properies) {
			$output .= $selector . ' {' . PHP_EOL;
			foreach ($properies as $property => $values) {
				foreach ((array) $values as $value) {
					$output .= "\t" . $property . ': ' . $value . ';' . PHP_EOL;
				}
			}
			$output .= '}' . PHP_EOL;
		}
		return $output;
	}
}
