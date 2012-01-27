<?php

abstract class CM_CssAdapter_Abstract {

	/**
	 * @var CM_Render
	 */
	protected $_render = null;

	/**
	 * @var CM_Css
	 */
	protected $_presets = null;

	/**
	 * @var string|null
	 */
	protected $_prefix = null;

	/**
	 * @var string
	 */
	protected $_css = null;

	/**
	 * @param string		  $css
	 * @param CM_Render	   $render
	 * @param CM_Css|null	 $presets
	 * @param string|null	 $prefix
	 */
	public function __construct($css, CM_Render $render, CM_Css $presets = null, $prefix = null) {
		$this->_render = $render;
		$this->_presets = $presets;
		$this->_prefix = $prefix;
		$this->_css = $css;
	}

	/**
	 * @return array
	 */
	abstract public function getRules();
}
