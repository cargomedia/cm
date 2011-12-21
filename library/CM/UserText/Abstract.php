<?php

abstract class CM_UserText_Abstract {
	private $_text;

	protected $_singleTags = array('br', 'img');
	protected $_allowedAttrs = array();
	protected $_allowedTags = array();
	protected $_internalTags = array();

	protected $_wrapLength = 5;

	function __construct($text) {
		$this->_text = (string) $text;
	}

	/**
	 * @param int $lengthMax OPTIONAL
	 * @return string Formated text with all tags escaped
	 */
	public function getPlain($lengthMax = null) {
		$text = $this->_text;
		$text = $this->_escape($text);
		$text = $this->_getFormat($text, true, $lengthMax);
		return $text;
	}

	/**
	 * @param int $lengthMax OPTIONAL
	 * @return string Formated text with allowed tags preserved, un-allowed tags escaped and smilies images inserted
	 */
	public function getFormat($lengthMax = null) {
		$text = $this->_text;
		$text = nl2br($text);
		$text = $this->_escapeUnAllowedTags($text);
		$text = $this->_insertEmoticonTags($text);
		$text = $this->_getFormat($text, false, $lengthMax);
		$text = $this->_insertEmoticonHtml($text);
		return $text;
	}

	/**
	 * @param int $lengthMax OPTIONAL
	 * @return string Formated text with allowed tags stripped, un-allowed tags escaped and smilies as text
	 */
	public function getFormatPlain($lengthMax = null) {
		$text = $this->_text;
		$text = $this->_escapeUnAllowedTags($text);
		$text = $this->_insertEmoticonTags($text);
		$text = $this->_getFormat($text, true, $lengthMax);
		$text = $this->_insertEmoticonHtml($text);
		return $text;
	}

	private function _getFormat($text, $stripAllowedTags = false, $lengthMax = null) {
		$domDoc = new DOMDocument();
		@$domDoc
				->loadHTML(
						'<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		</head><body>' . $text . '</body></html>');
		$domBody = $domDoc->childNodes->item(1)->childNodes->item(1);
		$text = $this->_collapseDomTree($domBody, $stripAllowedTags, $lengthMax);
		return $text;
	}

	private function _getEmoticonData() {
		$cacheKey = CM_CacheConst::Usertext_Emoticons;
		if (($emoticons = CM_CacheLocal::get($cacheKey)) === false) {
			$emoticons = array('codes' => array(), 'tags' => array(), 'htmls' => array());
			foreach (new CM_Paging_Smiley_All() as $smiley) {
				foreach ($smiley['codes'] as $code) {
					$path = URL_STATIC . 'img/smiles/' . $smiley['path'] . '?' . Config::get()->modified;
					$emoticons['codes'][] = $code;
					$emoticons['tags'][] = '<emoticon>' . $smiley['id'] . '</emoticon>';
					$emoticons['htmls'][] = '<img class="smile" alt="' . $code . '" title="' . $code . '" src="' . $path . '" />';
				}
			}
			CM_CacheLocal::set($cacheKey, $emoticons);
		}
		return $emoticons;
	}

	private function _insertEmoticonTags($text) {
		$emoticons = $this->_getEmoticonData();
		return str_replace($emoticons['codes'], $emoticons['tags'], $text);
	}

	private function _insertEmoticonHtml($text) {
		$emoticons = $this->_getEmoticonData();
		return str_replace($emoticons['tags'], $emoticons['htmls'], $text);
	}

	private function _censor($text) {
		$cacheKey = CM_CacheConst::Usertext_Badwords;
		if (($badwords = CM_CacheLocal::get($cacheKey)) === false) {
			$badwords = array('search' => array(), 'replace' => CM_Language::text('txt.badword_replacement'));
			foreach (new CM_Paging_ContentList_Badwords() as $badword) {
				$badword = str_replace('*', '[^\s]*', $badword);
				$badwords['search'][] = '#(\b' . $badword . '\b)#i';
			}

			CM_CacheLocal::set($cacheKey, $badwords);
		}

		return preg_replace($badwords['search'], $badwords['replace'], $text);
	}

	private function _wrap($text) {
		$length = $this->_wrapLength;
		return preg_replace('/([^\s-]{' . $length . '})([^\s-]{' . $length . '})/u', '$1' . self::getSplitChar() . '$2', $text);
	}

	private function _escape($text) {
		return htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
	}

	private function _escapeUnAllowedTags($text) {
		$_allowedTags = $this->_allowedTags;
		return preg_replace_callback('#<[/\s]*(?<tag>\w*).*?>#',
				function ($matches) use ($_allowedTags) {
					if ($matches['tag'] && in_array($matches['tag'], $_allowedTags)) {
						return $matches[0];
					} else {
						return htmlspecialchars($matches[0], ENT_COMPAT, 'UTF-8');
					}
				}, $text);
	}

	/**
	 * @param DomNode $domNode
	 * @param boolean $stripAllowedTags
	 * @param int|null &$lengthMax
	 */
	private function _collapseDomTree(DomNode $domNode, $stripAllowedTags, &$lengthMax, $level = 0) {
		$result = '';

		if (null !== $lengthMax && $lengthMax <= 0) {
			return '';
		}

		if ($domNode->hasChildNodes()) {
			foreach ($domNode->childNodes as $domNode) {
				if ($domNode->nodeType == XML_TEXT_NODE) {
					if (null === $lengthMax || $lengthMax >= 0) {
						$nodeValue = $domNode->nodeValue;
						$nodeValue = $this->_censor($nodeValue);
						$length = mb_strlen($nodeValue);
						if (null !== $lengthMax) {
							$lengthAvailable = $lengthMax;
							$lengthMax -= $length;
							if ($length > $lengthAvailable) {
								$nodeValue = preg_replace('/\s+?(\S+)?$/u', '', mb_substr($nodeValue, 0, $lengthAvailable + 1));
								$nodeValue = mb_substr($nodeValue, 0, $lengthAvailable);
							}
						}
						$nodeValue = $this->_wrap($nodeValue);
						$nodeValue = $this->_escape($nodeValue);

						$result .= $nodeValue;
					}

				} elseif ($domNode->nodeType == XML_ELEMENT_NODE) {
					if (null === $lengthMax || $lengthMax > 0) {
						$childNodeResult = $this->_collapseDomTree($domNode, $stripAllowedTags, $lengthMax, $level + 1);

						if (in_array($domNode->nodeName, $this->_internalTags)) {
							$result .= '<' . $domNode->nodeName . '>' . $childNodeResult . '</' . $domNode->nodeName . '>';

						} else {
							$attributeStr = '';
							// remove unallowed tag attributes
							foreach ($domNode->attributes as $attribute) {
								$attributeName = $attribute->nodeName;
								if (in_array($attributeName, $this->_allowedAttrs)) {
									$attributeValue = $attribute->nodeValue;

									$attributeValue = str_replace('data:', '', $attributeValue);
									$attributeValue = str_replace('javascript:', '', $attributeValue);

									$attributeValue = $this->_censor($attributeValue);
									$attributeValue = $this->_escape($attributeValue);

									$attributeStr .= ' ' . $attributeName . '="' . $attributeValue . '"';
								}
							}

							if (in_array($domNode->nodeName, $this->_singleTags)) {
								$startTag = '<' . $domNode->nodeName . $attributeStr . ' />';
								$endTag = '';
							} else {
								$startTag = '<' . $domNode->nodeName . $attributeStr . '>';
								$endTag = '</' . $domNode->nodeName . '>';
							}

							// skip allowed tags if they are to be stripped, otherwise escape tag
							if (in_array($domNode->nodeName, $this->_allowedTags)) {
								if ($stripAllowedTags) {
									$startTag = '';
									$endTag = '';
								}
							} else {
								$startTag = $this->_escape($startTag);
								$endTag = $this->_escape($endTag);
							}

							$result .= $startTag . $childNodeResult . $endTag;
						}
					}
				}
			}
		}

		if ($level == 0 && null !== $lengthMax) {
			if ($lengthMax < 0) {
				$result .= '…';
			}
		}

		return $result;
	}

	/**
	 * @return string invisible split char
	 */
	public static function getSplitChar() {
		return html_entity_decode('&#8203;', ENT_COMPAT, 'UTF-8');
	}

}
