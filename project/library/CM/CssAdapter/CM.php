<?php

class CM_CssAdapter_CM extends CM_CssAdapter_Abstract {
	const REGEX_SELECTORS = '[@*+\$\.\#\w<>:\~]+[*+\$\.\#\w\-<>\:\[="\'\],\s\~\(\)]+';
	const REGEX_PROPERTY = '[a-z\-]+';
	const REGEX_VALUE = '[^;]+';
	const REGEX_SPLIT_SELECTORS = '/^(.+)\s*(?:(?-U)\<\<\s*([\$\w\.\s,-]+))?$/sU';
	const REGEX_COLOR = '(?:(?:\#(\w{1})(\w{1})(\w{1}))|(?:\#(\w{2})(\w{2})(\w{2}))|(?:rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d\.]+))?\))|(?:(\w{2,40})))';

	/**
	 * @return array
	 */
	public function getRules() {
		$presetRules = array();
		if ($this->_presets) {
			$presetRules = $this->_presets->getRules();
		}
		return $this->_parseCssString($this->_css, $presetRules, $this->_prefix);
	}

	/**
	 * @param string	  $css
	 * @param array|null  $presets
	 * @param string|null $prefix
	 * @return array
	 */
	private function _parseCssString($css, array $presets = null, $prefix = null) {
		$css = preg_replace('~/\*.+\*/~sU', PHP_EOL, $css);

		preg_match_all('~(' . self::REGEX_SELECTORS . ')?\s*\{([^\}]+)\}~isU', $css, $matches, PREG_SET_ORDER);
		$output = array();
		foreach ($matches as $match) {
			preg_match(self::REGEX_SPLIT_SELECTORS, $match[1], $splitMatch);
			$presetNames = preg_split('~\s*,\s*~', $splitMatch[2], -1, PREG_SPLIT_NO_EMPTY);
			$selectors = preg_split('~\s*,\s*~', $splitMatch[1], -1, PREG_SPLIT_NO_EMPTY);
			$rules = $this->_parseRules($match[2], $presets, $presetNames);
			if (count($selectors)) {
				foreach ($selectors as &$selector) {
					if ('@' == $selector) {
						$selector = '';
					}
					if ($prefix) {
						$selector = $prefix . ' ' . $selector;
					}
					$selector = trim($selector);
				}
				$selector = implode(', ', $selectors);
				if (!isset($output[$selector])) {
					$output[$selector] = $rules;
				} else {
					$output[$selector] = array_merge($output[$selector], $rules);
				}
			} else {
				if (!$prefix) {
					throw new CM_Exception('Blocks without selectors are not allowed unless a prefix is defined!');
				}
				$output[$prefix] = $rules;
			}
		}
		return $output;
	}

	/**
	 * @param string	  $cssBlock
	 * @param array|null  $presets
	 * @param array|null  $presetNames
	 * @return array
	 * @throws CM_Exception
	 */
	private function _parseRules($cssBlock, array $presets = null, array $presetNames = null) {
		if (!$presetNames) {
			$presetNames = array();
		}
		$properties = array();
		foreach ($presetNames as $presetName) {
			if (!isset($presets[$presetName])) {
				throw new CM_Exception('Undefined preset `' . $presetName . '`');
			}
			$properties = array_merge($properties, $presets[$presetName]);
		}

		preg_match_all('~\b(' . self::REGEX_PROPERTY . ')\s*:\s*(' . self::REGEX_VALUE . ');?\s*~i', $cssBlock, $rules, PREG_SET_ORDER);
		foreach ($rules as $rule) {
			$property = strtolower($rule[1]);
			$value = $rule[2];
			switch ($property) {
				case 'background':
					if (preg_match('#(?:image)\(([^\)\s]+)\)#', $value, $match)) {
						$url = $this->_render->getUrlImg($match[1]);
						$value = str_replace($match[0], 'url(' . $url . ')', $value);
					}
					break;
				case 'background-image':
					if (preg_match('#(?:image)\(([^\)\s]+)\)#', $value, $match)) {
						$url = $this->_render->getUrlImg($match[1]);
						$value = str_replace($match[0], 'url(' . $url . ')', $value);
					}
					if (preg_match('#^linear-gradient\((?<point>.+?),\s*(?<color1>' . self::REGEX_COLOR . '),\s*(?<color2>' . self::REGEX_COLOR .
							')\)$#i', $value, $match)
					) {
						$point = $match['point'];
						$color1 = $this->_parseColor($match['color1']);
						$color2 = $this->_parseColor($match['color2']);

						$color1str = $this->_printColor($color1);
						$color1strHex = $this->_printColor($color1, true);
						$color2str = $this->_printColor($color2);
						$color2strHex = $this->_printColor($color2, true);
						$value = array();
						$value[] = 'linear-gradient(' . $point . ',' . $color1str . ',' . $color2str . ')';
						$value[] = '-moz-linear-gradient(' . $point . ',' . $color1str . ',' . $color2str . ')';
						$value[] = '-webkit-linear-gradient(' . $point . ',' . $color1str . ',' . $color2str . ')';
						$value[] = '-o-linear-gradient(' . $point . ',' . $color1str . ',' . $color2str . ')';

						if ($point == 'top' || $point == 'left') {
							if ($point == 'left') {
								$points = 'left top,right top';
							}
							if ($point == 'top') {
								$points = 'left top,left bottom';
							}
							$value[] = '-webkit-gradient(linear,' . $points . ',from(' . $color1str . '),to(' . $color2str . '))';
						}

						// @see http://msdn.microsoft.com/en-us/library/ms532997(VS.85,loband).aspx
						$filterType = 0;
						if ($point == 'left') {
							$filterType = 1;
						}
						$properties['filter'] = $this->_getFilterProperty('progid:DXImageTransform.Microsoft.gradient',
								'GradientType=' . $filterType . ',startColorstr=' . $color1strHex . ',endColorstr=' . $color2strHex, $properties);
					}
					break;
				case 'background-color':
					if ('transparent' != $value && 'inherit' != $value) {
						$color = $this->_parseColor($value);
						$value = $this->_printColor($color);
						if ($color->getAlpha() < 1) {
							$colorStrHex = $this->_printColor($color, true);
							$properties['filter'] = $this->_getFilterProperty('progid:DXImageTransform.Microsoft.gradient',
									'GradientType=0,startColorstr=' . $colorStrHex . ',endColorstr=' . $colorStrHex, $properties);
						}
					}
					break;
				case 'opacity':
					$value = round($value, 2);
					$properties['filter'] = $this->_getFilterProperty('alpha', 'opacity=' . ($value * 100), $properties);
					break;
				case 'border-radius':
					$properties['-moz-border-radius'] = $value;
					break;
				case 'box-shadow':
					$properties['-moz-box-shadow'] = $value;
					$properties['-webkit-box-shadow'] = $value;
					break;
				case 'box-sizing':
					$properties['-moz-box-sizing'] = $value;
					$properties['-webkit-box-sizing'] = $value;
					break;
				case 'user-select':
					$properties['-moz-user-select'] = $value;
					$properties['-webkit-user-select'] = $value;
					break;
				case 'transform':
					$properties['-moz-transform'] = $value;
					$properties['-webkit-transform'] = $value;
					break;
				case 'transition':
					$properties['-moz-transition'] = $value;
					$properties['-webkit-transition'] = $value;
					break;
			}

			$properties[$property] = $value;
		}
		return $properties;
	}

	/**
	 * Return a MS-filter property
	 *
	 * @param string	 $name	   Filter-name
	 * @param string	 $value	  Filter-value
	 * @param array|null $properties Existing properties to use
	 * @return string
	 */
	private function _getFilterProperty($name, $value, array $properties = null) {
		$result = isset($properties['filter']) ? $properties['filter'] : '';
		if (preg_match('/' . $name . '\(.*?\)/i', $result)) {
			$result = preg_replace('/(' . $name . ')\(.*?\)/i', $name . '(' . $value . ')', $result);
		} elseif (!empty($result)) {
			$result = $name . '(' . $value . ') ' . $result;
		} else {
			$result = $name . '(' . $value . ')';
		}
		return $result;
	}

	/**
	 * @param string $colorStr
	 * @return CM_Color
	 * @throws CM_Exception
	 */
	private function _parseColor($colorStr) {
		if (!preg_match('#^' . self::REGEX_COLOR . '$#', $colorStr, $match)) {
			throw new CM_Exception('Cannot parse color `' . $colorStr . '`');
		}
		if (strlen($match[1]) && strlen($match[2]) && strlen($match[3])) {
			return new CM_Color(hexdec($match[1]) * 17, hexdec($match[2]) * 17, hexdec($match[3]) * 17);
		} elseif (strlen($match[4]) && strlen($match[5]) && strlen($match[6])) {
			return new CM_Color(hexdec($match[4]), hexdec($match[5]), hexdec($match[6]));
		} elseif (strlen($match[7]) && strlen($match[8]) && strlen($match[9])) {
			$alpha = strlen($match[10]) ? (float) $match[10] : null;
			return new CM_Color($match[7], $match[8], $match[9], $alpha);
		} elseif (strlen($match[11])) {
			return CM_Color::parseX11($match[11]);
		} else {
			throw new CM_Exception('Cannot parse color `' . $colorStr . '`.');
		}
	}

	/**
	 * @param CM_Color  $color
	 * @param bool|null $forceAlphaHex
	 * @return string
	 */
	private function _printColor(CM_Color $color, $forceAlphaHex = null) {
		if ($color->getAlpha() == 1) {
			return '#' . $this->_printColorHexComponent($color->getRed()) . $this->_printColorHexComponent($color->getGreen()) .
					$this->_printColorHexComponent($color->getBlue());
		}
		if ($forceAlphaHex) {
			// @see http://msdn.microsoft.com/en-us/library/ms532930(v=vs.85).aspx
			return '#' . $this->_printColorHexComponent($color->getAlpha() * 255) . $this->_printColorHexComponent($color->getRed()) .
					$this->_printColorHexComponent($color->getGreen()) . $this->_printColorHexComponent($color->getBlue());
		}
		return 'rgba(' . $color->getRed() . ',' . $color->getGreen() . ',' . $color->getBlue() . ',' . $color->getAlpha() . ')';
	}

	/**
	 * @param int $value
	 * @return string
	 */
	private function _printColorHexComponent($value) {
		return str_pad(dechex($value), 2, '0', STR_PAD_LEFT);
	}

}