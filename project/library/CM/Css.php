<?php

class CM_Css {
	const REGEX_SELECTORS = '[*+\$\.\#\w<>:\~]+[*+\$\.\#\w\-<>\:\[="\'\],\s\~\(\)]+';
	const REGEX_PROPERTY = '[a-z\-]+';
	const REGEX_VALUE = '[^;]+';
	const REGEX_SPLIT_SELECTORS = '/^(.+)\s*(?:(?-U)\<\<\s*([\$\w\.\s,-]+))?$/sU';
	const REGEX_COLOR = 'rgba?\(\s*(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\s*,\s*(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\s*,\s*(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])(?:\s*,\s*(0|1|0?\.\d+))?\s*\)';
	private $_data = array();

	/**
	 * @var CM_Render
	 */
	private $_render = null;

	/**
	 * @param string		  $css
	 * @param CM_Render	   $render
	 * @param CM_Css|null	 $presets
	 * @param string|null	 $prefix
	 */
	public function __construct($css, CM_Render $render, CM_Css $presets = null, $prefix = null) {
		if ($presets) {
			$presets = $presets->getData();
		}
		$this->_render = $render;
		$this->_data = $this->_parseCssString($css, $presets, $prefix);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$output = '';
		foreach ($this->_data as $selectors => $properies) {
			$output .= "$selectors {" . PHP_EOL;
			foreach ($properies as $property => $values) {
				foreach ((array) $values as $value) {
					$output .= "\t$property: $value;" . PHP_EOL;
				}
			}
			$output .= "}" . PHP_EOL;
		}
		return $output;
	}

	/**
	 * @return array
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * @param string $css
	 * @param array  $presets OPTIONAL
	 * @param string $prefix  OPTIONAL
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
				if ($prefix) {
					foreach ($selectors as &$selector) {
						$selector = $prefix . ' ' . $selector;
					}
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
		foreach ($presetNames as $selector) {
			if (!isset($presets[$selector])) {
				throw new CM_Exception("Undefined preset `$selector`");
			}
			$properties = array_merge($properties, $presets[$selector]);
		}

		preg_match_all('~\b(' . self::REGEX_PROPERTY . ')\s*:\s*(' . self::REGEX_VALUE . ');?\s*~i', $cssBlock, $rules, PREG_SET_ORDER);
		foreach ($rules as $rule) {
			$property = strtolower($rule[1]);
			$value = $rule[2];
			switch ($property) {
				case 'background':
				case 'background-image':
					if (preg_match('~(?:image)\(([^\)\s]+)\)~', $value, $match)) {
						list($imgMatch, $filename) = $match;
						$imageURL = $this->_render->getUrlImg($filename);
						$value = str_replace($imgMatch, "url($imageURL)", $value);
					}
					if (preg_match('#^linear-gradient\((?<point>.+?),\s*(?<color1>.+?),\s*(?<color2>.+?)\)$#i', $value, $match)) {
						$point = $match['point'];
						$color1 = $this->_getColor($match['color1']);
						$color1Hex = $this->_getColor($match['color1'], true);
						$color2 = $this->_getColor($match['color2']);
						$color2Hex = $this->_getColor($match['color2'], true);
						$value = array();
						$value[] = 'linear-gradient(' . $point . ',' . $color1 . ',' . $color2 . ')';
						$value[] = '-moz-linear-gradient(' . $point . ',' . $color1 . ',' . $color2 . ')';
						$value[] = '-webkit-linear-gradient(' . $point . ',' . $color1 . ',' . $color2 . ')';
						$value[] = '-o-linear-gradient(' . $point . ',' . $color1 . ',' . $color2 . ')';

						if ($point == 'top' || $point == 'left') {
							if ($point == 'left') {
								$points = 'left top,right top';
							}
							if ($point == 'top') {
								$points = 'left top,left bottom';
							}
							$value[] = '-webkit-gradient(linear,' . $points . ',from(' . $color1 . '),to(' . $color2 . '))';
						}

						// MS Filter: http://msdn.microsoft.com/en-us/library/ms532997(VS.85,loband).aspx
						$filterType = 0;
						if ($point == 'left') {
							$filterType = 1;
						}
						$properties['filter'] = $this->_getFilterProperty('progid:DXImageTransform.Microsoft.gradient',
								'GradientType=' . $filterType . ',startColorstr=' . $color1Hex . ',endColorstr=' . $color2Hex, $properties);
					}
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
				case 'opacity':
					$value = round($value, 2);
					$properties['filter'] = $this->_getFilterProperty('alpha', 'opacity=' . ($value * 100), $properties);
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
	 * @param string	$colorStr
	 * @param bool|null $forceHex
	 * @return string
	 */
	private function _getColor($colorStr, $forceHex = null) {
		$regexDec = '/^rgba?\(\s*(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\s*,\s*(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\s*,\s*(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])(?:\s*,\s*(0|1|0?\.\d+))?\s*\)$/';
		$regexHex = '/^#([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})$/';
		$red = $green = $blue = $alpha = null;
		if (preg_match($regexHex, $colorStr, $match)) {
			$red = hexdec($match[1]);
			$green = hexdec($match[2]);
			$blue = hexdec($match[3]);
			$alpha = 1;
		} elseif (preg_match($regexDec, $colorStr, $match)) {
			$red = $match[1];
			$green = $match[2];
			$blue = $match[3];
			$alpha = isset($match[4]) ? $match[4] : 1;
		} else {
			throw new CM_Exception('Cannot parse color `' . $colorStr . '`');
		}
		if ($forceHex || $alpha == 1) {
			return '#' . str_pad(dechex($red), 2, '0', STR_PAD_LEFT) . str_pad(dechex($green), 2, '0', STR_PAD_LEFT) .
					str_pad(dechex($blue), 2, '0', STR_PAD_LEFT);
		}
		return 'rgba(' . $red . ',' . $green . ',' . $blue . ', ' . $alpha . ')';
	}
}
