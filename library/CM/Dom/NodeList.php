<?php

class CM_Dom_NodeList {

	/** @var DOMDocument */
	private $_doc;

	/** @var DOMElement */
	private $_element;

	/** @var DOMXPath */
	private $_xpath;

	/**
	 * @param string|DOMElement $html
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($html) {
		if ($html instanceof DOMElement) {
			$this->_element = $html;
			$this->_doc = $html->ownerDocument;
		} else {
			$html = (string) $html;
			$this->_doc = new DOMDocument();
			$html = '<?xml version="1.0" encoding="UTF-8"?>' . $html;
			try {
				$this->_doc->loadHTML($html);
			} catch (ErrorException $e) {
				throw new CM_Exception_Invalid('Cannot load html');
			}
			$this->_element = $this->_doc->documentElement;
		}
	}

	/**
	 * @return CM_Dom_NodeList[]
	 */
	public function getChildren() {
		$childList = array();
		foreach ($this->_element->childNodes as $childNode) {
			$childList[] = new self($childNode);
		}
		return $childList;
	}

	/**
	 * @return string
	 */
	public function getText() {
		return $this->_element->textContent;
	}

	/**
	 * @param string $name
	 * @return string|null
	 */
	public function getAttribute($name) {
		$element = $this->_element;
		if (!$element->hasAttribute($name)) {
			return null;
		}
		return $element->getAttribute($name);
	}

	/**
	 * @return string[]
	 */
	public function getAttributeList() {
		$attributeList = array();
		foreach ($this->_element->attributes as $key => $attrNode) {
			$attributeList[$key] = $attrNode->value;
		}
		return $attributeList;
	}

	/**
	 * @param string $selector
	 * @return CM_Dom_NodeList
	 */
	public function findElement($selector) {
		$elements = $this->_findAll($selector);
		if ($elements->length < 1) {
			return null;
		}
		$element = $elements->item(0);
		return new self($element);
	}

	/**
	 * @param string $selector
	 * @return bool
	 */
	public function has($selector){
		$elements = $this->_findAll($selector);
		return ($elements->length > 0);
	}

	/**
	 * @return DOMXPath
	 */
	private function _getXPath() {
		if (!$this->_xpath) {
			$this->_xpath = new DOMXPath($this->_doc);
		}
		return $this->_xpath;
	}

	/**
	 * @param string $selector
	 * @return DOMNodeList
	 */
	private function _findAll($selector) {
		$xpath = '//' . preg_replace('-([^>\s])\s+([^>\s])-', '$1//$2', trim($selector));
		$xpath = preg_replace('/([^\s]+)\s*\>\s*([^\s]+)/', '$1/$2', $xpath);
		$xpath = preg_replace('/\[([^~=\[\]]+)~="([^~=\[\]]+)"\]/', '[contains(concat(" ",@$1," "),concat(" ","$2"," "))]', $xpath);
		$xpath = preg_replace('/\[([^~=\[\]]+)="([^~=\[\]]+)"\]/', '[@$1="$2"]', $xpath);
		$xpath = preg_replace('/\[([\w-]+)\]/', '[@$1]', $xpath);
		$xpath = str_replace(':last', '[last()]', str_replace(':first', '[1]', $xpath));
		$xpath = preg_replace('/:eq\((\d+)\)/e', '"[".("$1"+1)."]"', $xpath);
		$xpath = preg_replace('/\.([\w-]*)/', '[contains(concat(" ",@class," "),concat(" ","$1"," "))]', $xpath);
		$xpath = preg_replace('/#([\w-]*)/', '[@id="$1"]', $xpath);
		$xpath = preg_replace('-\/\[-', '/*[', $xpath);
		return $this->_getXPath()->query($xpath, $this->_element);
	}
}
