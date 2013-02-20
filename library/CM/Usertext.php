<?php

class CM_Usertext extends CM_Class_Abstract {
	private $_text;

	private $_singleTags = array('area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source',
		'track', 'wbr');
	private $_allowedAttrs = array('alt', 'class', 'height', 'href', 'src', 'title', 'width');
	private $_allowedTags = array('b', 'i', 'q', 'span', 'u', 'br', 'img');
	private $_internalTags = array('emoticon');
	private $_wrapLength = 5;

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
		$text = $this->_getFormat($text, array(), array(), $lengthMax);
		return $text;
	}

	/**
	 * @param int|null	  $lengthMax
	 * @param string[]|null $allowedTags
	 * @param string[]|null $visibleTags
	 * @return string Formated text with allowed tags preserved, un-allowed tags escaped and smilies images inserted
	 */
	public function getFormat($lengthMax = null, array $allowedTags = null, array $visibleTags = null) {
		if (null === $allowedTags) {
			$allowedTags = $this->_allowedTags;
		}
		if (null === $visibleTags) {
			$visibleTags = $allowedTags;
		}
		$text = $this->_text;
		$text = $this->_nl2br($text, 3);
		$text = $this->_insertEmoticonTags($text);
		$text = $this->_escapeUnAllowedTags($text, $allowedTags);
		$text = $this->_getFormat($text, $allowedTags, $visibleTags, $lengthMax);
		$text = $this->_insertEmoticonHtml($text);
		return $text;
	}

	/**
	 * @param int|null	  $lengthMax
	 * @return string Formated text with allowed tags stripped, un-allowed tags escaped and smilies as text
	 */
	public function getFormatPlain($lengthMax = null) {
		return $this->getFormat($lengthMax, null, array());
	}

	/**
	 * @param string   $text
	 * @param string[] $allowedTags
	 * @param string[] $visibleTags
	 * @param int|null $lengthMax
	 * @return string
	 */
	private function _getFormat($text, array $allowedTags, array $visibleTags, $lengthMax = null) {
		$domDoc = new DOMDocument();
		@$domDoc->loadHTML('<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $text .
				'</body></html>');
		$domBody = $domDoc->childNodes->item(1)->childNodes->item(1);
		$text = $this->_collapseDomTree($domBody, $allowedTags, $visibleTags, $lengthMax);
		return $text;
	}

	/**
	 * @return array
	 */
	private function _getEmoticonData() {
		$cacheKey = CM_CacheConst::Usertext_Filter_Emoticons;
		if (($emoticons = CM_CacheLocal::get($cacheKey)) === false) {
			$emoticons = array('codes' => array(), 'tags' => array(), 'htmls' => array());
			foreach (new CM_Paging_Smiley_All() as $smiley) {
				foreach ($smiley['codes'] as $code) {
					$emoticons['codes'][] = $code;
					$emoticons['tags'][] = '<emoticon>' . $smiley['id'] . '</emoticon>';
					$emoticons['htmls'][] =
							'<span class="smiley smiley-' . $smiley['id'] . ' smileySet-' . $smiley['setId'] . '" title="' . $this->_escape($code) .
									'"></span>';
				}
			}
			CM_CacheLocal::set($cacheKey, $emoticons);
		}
		return $emoticons;
	}

	/**
	 * @param string $text
	 * @return mixed
	 */
	private function _insertEmoticonTags($text) {
		$emoticons = $this->_getEmoticonData();
		return str_replace($emoticons['codes'], $emoticons['tags'], $text);
	}

	/**
	 * @param string $text
	 * @return mixed
	 */
	private function _insertEmoticonHtml($text) {
		$emoticons = $this->_getEmoticonData();
		return str_replace($emoticons['tags'], $emoticons['htmls'], $text);
	}

	/**
	 * @param string $text
	 * @return mixed
	 */
	private function _censor($text) {
		$cacheKey = CM_CacheConst::Usertext_Filter_Badwords;
		if (($badwords = CM_CacheLocal::get($cacheKey)) === false) {
			$badwords = array('search' => array(), 'replace' => '…');
			foreach (new CM_Paging_ContentList_Badwords() as $badword) {
				$badword = preg_quote($badword, '#');
				$badword = str_replace('\*', '[^\s]*', $badword);
				$badwords['search'][] = '#(\b' . $badword . '\b)#i';
			}

			CM_CacheLocal::set($cacheKey, $badwords);
		}

		return preg_replace($badwords['search'], $badwords['replace'], $text);
	}

	/**
	 * @param string $text
	 * @return mixed
	 */
	private function _wrap($text) {
		$length = $this->_wrapLength;
		return preg_replace('/([^\s-]{' . $length . '})([^\s-]{' . $length . '})/u', '$1' . self::getSplitChar() . '$2', $text);
	}

	/**
	 * @param string $text
	 * @return string
	 */
	private function _escape($text) {
		return htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * @param string   $text
	 * @param int|null $countMax
	 * @return string
	 */
	private function _nl2br($text, $countMax = null) {
		$text = str_replace("\r", '', $text);
		if (null !== $countMax) {
			$countMax = (int) $countMax;
			$text = preg_replace('#(\n{' . $countMax . '})\n+#', '$1', $text);
		}
		$text = trim($text, "\n");
		$text = str_replace("\n", "<br />\n", $text);
		return $text;
	}

	/**
	 * Escape unallowed tags before creating a DOM tree of the input, to make sure
	 * we don't create (closing-)tags for them (e.g. "<foo>bar" -> "<foo>bar</foo>")
	 *
	 * @param string	$text
	 * @param string[]  $allowedTags
	 * @return string
	 */
	private function _escapeUnAllowedTags($text, array $allowedTags) {
		$_allowedTags = array_merge($allowedTags, $this->_internalTags);
		return preg_replace_callback('#<[/\s]*(?<tag>\w*).*?>#', function ($matches) use ($_allowedTags) {
			if ($matches['tag'] && in_array($matches['tag'], $_allowedTags)) {
				return $matches[0];
			} else {
				return htmlspecialchars($matches[0], ENT_COMPAT, 'UTF-8');
			}
		}, $text);
	}

	/**
	 * @param DomNode   $domNode
	 * @param string[]  $allowedTags
	 * @param string[]  $visibleTags
	 * @param int|null  &$lengthMax
	 * @param int|null  $level
	 * @return string
	 */
	private function _collapseDomTree(DomNode $domNode, array $allowedTags, array $visibleTags, &$lengthMax, $level = null) {
		if (is_null($level)) {
			$level = 0;
		}
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

						if (in_array($domNode->nodeName, $this->_internalTags)) {
							$childNodeResult = $domNode->nodeValue;
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

							// skip allowed tags if they are not visible, otherwise escape tag
							if (in_array($domNode->nodeName, $allowedTags)) {
								if (!in_array($domNode->nodeName, $visibleTags)) {
									$startTag = '';
									$endTag = '';
								}
							} else {
								$startTag = $this->_escape($startTag);
								$endTag = $this->_escape($endTag);
							}

							$childNodeResult = $this->_collapseDomTree($domNode, $allowedTags, $visibleTags, $lengthMax, $level + 1);
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
