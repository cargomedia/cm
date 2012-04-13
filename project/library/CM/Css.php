<?php
require_once DIR_LIBRARY . 'lessphp/lessc.inc.php';

class CM_Css {

	/**
	 * @var string|null
	 */
	private $_css = null;
	/**
	 * @var string|null
	 */
	private $_prefix = null;
	/**
	 * @var CM_Css[]
	 */
	private $_children = array();

	/**
	 * @param string|null $css
	 * @param string|null $prefix
	 */
	public function __construct($css = null, $prefix = null) {
		if (!is_null($css)) {
			$this->_css = (string) $css;
		}
		if (!is_null($prefix)) {
			$this->_prefix = (string) $prefix;
		}
	}

	/**
	 * @param string      $css
	 * @param string|null $prefix
	 */
	public function add($css, $prefix = null) {
		$this->_children[] = new CM_Css($css, $prefix);
	}

	/**
	 * @param CM_Render $render
	 * @return string
	 */
	public function compile(CM_Render $render) {
		$lessc = new lessc();
		$lessc->registerFunction('image', function ($arg) use($render) {
			list($type, $path) = $arg;
			return array($type, ' url(' . $render->getUrlImg(substr($path, 1, -1)) . ')');
		});
		$output = $lessc->parse((string) $this);
		return $output;
	}

	public function __toString() {
		$content = '';
		if ($this->_prefix) {
			$content .= $this->_prefix . ' {' . PHP_EOL;
		}
		if ($this->_css) {
			$content .= $this->_css . PHP_EOL;
		}
		foreach ($this->_children as $css) {
			$content .= $css;
		}
		if ($this->_prefix) {
			$content .= '}' . PHP_EOL;
		}
		return $content;
	}
}
