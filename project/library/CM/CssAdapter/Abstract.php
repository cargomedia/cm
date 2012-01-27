<?php

abstract class CM_CssAdapter_Abstract {

	/**
	 * @var array
	 */
	private $_data = array();

	/**
	 * @var CM_Render
	 */
	private $_render = null;

	/**
	 * @param string		  $css
	 * @param CM_Render	   $render
	 * @param CM_Css|null	 $presets
	 * @param string|null	 $prefix
	 */
	public function __construct($css, CM_Render $render, CM_Css $presets = null, $prefix = null) {
		if ($presets) {
			$presets = $presets->getData();
		}
		$this->_render = $render;
		$this->_presets = $presets;
		$this->_prefix = $prefix;
		$this->_css = $css;
	}

	/**
	 * @param string $css
	 * @return string
	 */
	abstract public function parse();
}
