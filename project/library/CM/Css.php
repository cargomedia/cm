<?php
require_once DIR_LIBRARY . 'lessphp/lessc.inc.php';

class CM_Css {

	/**
	 * @var string
	 */
	private $_css = null;
	/**
	 * @var string|null
	 */
	private $_prefix = null;
	/**
	 * @var CM_Css[]
	 */
	public $_csss = array();

	/**
	 * @param string $css
	 * @param string|null $prefix
	 */
	public function __construct($css, $prefix = null) {
		$this->_css = (string) $css;
		$this->_prefix = (string) $prefix;
	}

	public function add(CM_Css $css) {
		if (count($this->_csss) == 0) {
			$this->_csss[0] = new CM_Css($this->_css);
		}
		$this->_csss[] = $css;
	}

	public function __toString() {
		$content = '';
		if ($this->_prefix) {
			$content .= $this->_prefix . ' {' . PHP_EOL;
		}
		if (count($this->_csss)) {
			foreach ($this->_csss as $css) {
				$content .= $css;
			}
		} else {
			$content .= $this->_css . PHP_EOL;
		}
		if ($this->_prefix) {
			$content .= '}' . PHP_EOL;
		}
		return $content;
	}

	public function compile() {
		$lessc = new lessc();
		$output = $lessc->parse((string) $this);
		return $output;
	}

}
