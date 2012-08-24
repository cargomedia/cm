<?php

class TH_Html {
	private $_doc;
	private $_xpath;
	private $_html;

	/**
	 * @param string $html
	 */
	public function __construct($html) {
		$this->_html = (string) $html;
	}

	/**
	 * @return string
	 */
	public function getHtml() {
		return $this->_html;
	}

	/**
	 * @param string $css
	 * @return string
	 */
	public function getText($css = '*') {
		$element = $this->_find($css);
		$text = $element->textContent;
		$text = preg_replace('/' . CM_Usertext::getSplitChar() . '/u', '', $text);
		return $text;
	}

	/**
	 * @param string $css
	 * @param string $name
	 * @return string
	 */
	public function getAttr($css, $name) {
		return $this->getText($css . ' @' . $name);
	}

	/**
	 * @param string $css
	 * @return int
	 */
	public function getCount($css = '*') {
		$elements = $this->_findAll($css);
		return $elements->length;
	}

	/**
	 * @param string $css
	 * @return array
	 */
	public function getTextAll($css = '*') {
		$elements = $this->_findAll($css);
		$texts = array();
		foreach ($elements as $element) {
			$texts[] = $element->textContent;
		}
		return $texts;
	}

	/**
	 * @param string $css
	 * @return boolean
	 */
	public function exists($css) {
		$element = $this->_find($css);
		return ($element !== null);
	}

	/**
	 * @return DOMDocument
	 */
	private function _getDocument() {
		if (!$this->_doc) {
			$this->_doc = new DOMDocument();
			@$this->_doc->loadHTML($this->getHtml());
		}
		return $this->_doc;
	}

	/**
	 * @return DOMXPath
	 */
	private function _getXPath() {
		if (!$this->_xpath) {
			$this->_xpath = new DOMXPath($this->_getDocument());
		}
		return $this->_xpath;
	}

	/**
	 * @param string $css
	 * @return DOMNode
	 */
	private function _find($css) {
		$elements = $this->_findAll($css);
		if ($elements->length < 1) {
			return null;
		}
		return $elements->item(0);
	}

	/**
	 * @param string $css
	 * @return DOMNodeList
	 */
	private function _findAll($css) {
		$xpath = '//' . preg_replace('-([^>\s])\s+([^>\s])-', '$1//$2', trim($css));
		$xpath = preg_replace('/([^\s]+)\s*\>\s*([^\s]+)/', '$1/$2', $xpath);
		$xpath = preg_replace('/\[([^~=\[\]]+)~="([^~=\[\]]+)"\]/', '[contains(concat(" ",@$1," "),concat(" ","$2"," "))]', $xpath);
		$xpath = preg_replace('/\[([^~=\[\]]+)="([^~=\[\]]+)"\]/', '[@$1="$2"]', $xpath);
		$xpath = preg_replace('/\[([\w-]+)\]/', '[@$1]', $xpath);
		$xpath = str_replace(':last', '[last()]', str_replace(':first', '[1]', $xpath));
		$xpath = preg_replace('/:eq\((\d+)\)/e', '"[".("$1"+1)."]"', $xpath);
		$xpath = preg_replace('/\.([\w-]*)/', '[contains(concat(" ",@class," "),concat(" ","$1"," "))]', $xpath);
		$xpath = preg_replace('/#([\w-]*)/', '[@id="$1"]', $xpath);
		$xpath = preg_replace('-\/\[-', '/*[', $xpath);
		return $this->_getXPath()->query($xpath);
	}
}
