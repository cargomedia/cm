<?php

class CM_Usertext_Usertext {

	/** @var CM_Render */
	private $_render;
	private $_maxLength = null;

	/**
	 * @param CM_Render $render
	 */
	function __construct(CM_Render $render) {
		$this->_render = $render;
	}

	/** @var CM_Usertext_Filter_Interface[] */
	private $_filterList = array();

	/**
	 * @param CM_Usertext_Filter_Interface $filter
	 */
	public function addFilter(CM_Usertext_Filter_Interface $filter) {
		$this->_filterList[] = $filter;
	}

	/**
	 * @param (int) $maxLength
	 */
	public function setMaxLength($maxLength) {
		if (null === $maxLength) {
			return;
		}
		$this->_maxLength = (int) $maxLength;
	}

	/**
	 * @param (string) $mode
	 * @throws CM_Exception_Invalid
	 */
	public function setMode($mode) {
		$mode = (string) $mode;
		$this->_clearFilters();
		$this->addFilter(new CM_Usertext_Filter_Escape());
		$this->addFilter(new CM_Usertext_Filter_Badwords());
		switch ($mode) {
			case 'oneline':
				$this->addFilter(new CM_Usertext_Filter_MaxLength($this->_maxLength));
				break;
			case 'simple':
				$this->addFilter(new CM_Usertext_Filter_MaxLength($this->_maxLength));
				$this->addFilter(new CM_Usertext_Filter_NewlineToLinebreak(3));
				break;
			case 'markdown':
				if (null !== $this->_maxLength) {
					throw new CM_Exception_Invalid('MaxLength is not allowed in mode markdown.');
				}
				$this->addFilter(new CM_Usertext_Filter_Emoticon_EscapeMarkdown());
				$this->addFilter(new CM_Usertext_Filter_Markdown(true));
				$this->addFilter(new CM_Usertext_Filter_Emoticon_UnescapeMarkdown());
				break;
			case 'markdownPlain':
				$this->addFilter(new CM_Usertext_Filter_Emoticon_EscapeMarkdown());
				$this->addFilter(new CM_Usertext_Filter_Markdown(true));
				$this->addFilter(new CM_Usertext_Filter_Emoticon_UnescapeMarkdown());
				$this->addFilter(new CM_Usertext_Filter_Striptags());
				$this->addFilter(new CM_Usertext_Filter_MaxLength($this->_maxLength));
				break;
			default:
				throw new CM_Exception_Invalid('Must define mode in Usertext.');
		}
		$this->addFilter(new CM_Usertext_Filter_Emoticon());
		if ('markdownPlain' != $mode) {
			$this->addFilter(new CM_Usertext_Filter_CutWhitespace());
		}
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function transform($text) {
		foreach ($this->_getFilters() as $filter) {
			$text = $filter->transform($text, $this->_render);
		}
		return $text;
	}

	private function _clearFilters() {
		$this->_filterList = array();
	}

	/**
	 * @return CM_Usertext_Filter_Interface[]
	 */
	private function _getFilters() {
		return $this->_filterList;
	}
}
