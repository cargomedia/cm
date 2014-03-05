<?php

class CM_Dom_NodeList implements Iterator, Countable {

  /** @var int */
  private $_iteratorPosition = 0;

  /** @var DOMDocument */
  private $_doc;

  /** @var DOMElement[] */
  private $_elementList = array();

  /** @var DOMXPath */
  private $_xpath;

  /**
   * @param string|DOMElement[] $html
   * @throws CM_Exception_Invalid
   */
  public function __construct($html) {
    if (is_array($html)) {
      foreach ($html as $element) {
        if (!$element instanceof DOMElement) {
          throw new CM_Exception_Invalid('Not all elements are DOMElement');
        }
        $this->_elementList[] = $element;
        if (!$this->_doc) {
          $this->_doc = $element->ownerDocument;
        }
      }
    } else {
      $html = (string) $html;

      if (empty($html)) {
        throw new CM_Exception_Invalid('Cant create elementList from empty string');
      }

      $this->_doc = new DOMDocument();
      $html = '<?xml version="1.0" encoding="UTF-8"?>' . $html;
      try {
        $this->_doc->loadHTML($html);
      } catch (ErrorException $e) {
        throw new CM_Exception_Invalid('Cannot load html');
      }
      $this->_elementList[] = $this->_doc->documentElement;
    }
  }

  /**
   * @return CM_Dom_NodeList
   */
  public function getChildren() {
    $childNodeList = array();
    foreach ($this->_elementList as $element) {
      foreach ($element->childNodes as $childNode) {
        $childNodeList[] = $childNode;
      }
    }
    return new self($childNodeList);
  }

  /**
   * @return string
   */
  public function getText() {
    $text = '';
    foreach ($this->_elementList as $element) {
      $text .= $element->textContent;
    }
    return $text;
  }

  /**
   * @param string $name
   * @return string|null
   */
  public function getAttribute($name) {
    $attributes = $this->getAttributeList();
    if (!isset($attributes[$name])) {
      return null;
    }
    return $attributes[$name];
  }

  /**
   * @return string[]
   */
  public function getAttributeList() {
    $attributeList = array();
    if (!isset($this->_elementList[0])) {
      return $attributeList;
    }
    foreach ($this->_elementList[0]->attributes as $key => $attrNode) {
      $attributeList[$key] = $attrNode->value;
    }
    return $attributeList;
  }

  /**
   * @param string $selector
   * @return CM_Dom_NodeList
   */
  public function find($selector) {
    $elements = $this->_findAll($selector);
    return new self($elements);
  }

  /**
   * @param string $selector
   * @return bool
   */
  public function has($selector) {
    $elements = $this->_findAll($selector);
    return (count($elements) > 0);
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
   * @throws CM_Exception_Invalid
   * @return DOMElement[]
   */
  private function _findAll($selector) {
    $xpath = '//' . preg_replace('-([^>\s])\s+([^>\s])-', '$1//$2', trim($selector));
    $xpath = preg_replace('/([^\s]+)\s*\>\s*([^\s]+)/', '$1/$2', $xpath);
    $xpath = preg_replace('/\[([^~=\[\]]+)~="([^~=\[\]]+)"\]/', '[contains(concat(" ",@$1," "),concat(" ","$2"," "))]', $xpath);
    $xpath = preg_replace('/\[([^~=\[\]]+)="([^~=\[\]]+)"\]/', '[@$1="$2"]', $xpath);
    $xpath = preg_replace('/\[([\w-]+)\]/', '[@$1]', $xpath);
    $xpath = str_replace(':last', '[last()]', str_replace(':first', '[1]', $xpath));
    $xpath = preg_replace_callback('/:eq\((\d+)\)/', function ($matches) {
      return '[' . ($matches[1] + 1) . ']';
    }, $xpath);
    $xpath = preg_replace('/\.([\w-]*)/', '[contains(concat(" ",@class," "),concat(" ","$1"," "))]', $xpath);
    $xpath = preg_replace('/#([\w-]*)/', '[@id="$1"]', $xpath);
    $xpath = preg_replace('-\/\[-', '/*[', $xpath);
    $nodes = array();
    foreach ($this->_elementList as $element) {
      foreach ($this->_getXPath()->query($xpath, $element) as $resultElement) {
        if (!$resultElement instanceof DOMElement) {
          throw new CM_Exception_Invalid('Xpath query does not return DOMElement');
        }
        $nodes[] = $resultElement;
      }
    }
    return $nodes;
  }

  public function current() {
    return new self(array($this->_elementList[$this->_iteratorPosition]));
  }

  public function next() {
    $this->_iteratorPosition++;
  }

  public function key() {
    return $this->_iteratorPosition;
  }

  public function valid() {
    return isset($this->_elementList[$this->_iteratorPosition]);
  }

  public function rewind() {
    $this->_iteratorPosition = 0;
  }

  public function count() {
    return count($this->_elementList);
  }
}
