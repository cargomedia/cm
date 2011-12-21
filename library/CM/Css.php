<?php

class CM_Css {
	const SELECTORS_REGEX = '[*+\$\.\#\w<>:\~]+[*+\$\.\#\w\-<>\:\[="\'\],\s\~]+';
	const PROPERTY_REGEX = '[a-z\-]+';
	const VALUE_REGEX = '[^;]+';
	const SPLIT_SELECTORS_REGEX = '/^(.+)\s*(?:(?-U)\<\<\s*([\$\w\.\s,-]+))?$/sU';
	private $_data = array();

	/**
	 * @var CM_Render
	 */
	private $_render = null;

	/**
	 * @param string $css
	 * @param CM_Css $presets OPTIONAL
	 * @param string $prefix OPTIONAL
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
	 * @param array $presets OPTIONAL
	 * @param string $prefix OPTIONAL
	 * @return array
	 */
	private function _parseCssString($css, array $presets = null, $prefix = null) {
		$css = preg_replace('~/\*.+\*/~sU', PHP_EOL, $css);
		preg_match_all('~(' . self::SELECTORS_REGEX . ')?\s*\{([^\}]+)\}~isU', $css, $matches, PREG_SET_ORDER);
		$output = array();
		foreach ($matches as $match) {
			preg_match(self::SPLIT_SELECTORS_REGEX, $match[1], $splitMatch);
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
	 *
	 * @param string $cssBlock
	 * @param array $presets OPTIONAL
	 * @param array $presetNames OPTIONAL
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

		preg_match_all('~\b(' . self::PROPERTY_REGEX . ')\s*:\s*(' . self::VALUE_REGEX . ');?\s*~i', $cssBlock, $rules, PREG_SET_ORDER);
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
					if (preg_match('#linear-gradient\((?<point>.+?),\s*(?<stop1>.+?),\s*(?<stop2>.+?)\)#i', $value, $match)) {
						$value = array();
						$value[] = 'linear-gradient(' . $match['point'] . ',' . $match['stop1'] . ',' . $match['stop2'] . ')';
						$value[] = '-moz-linear-gradient(' . $match['point'] . ',' . $match['stop1'] . ',' . $match['stop2'] . ')';
						$value[] = '-webkit-linear-gradient(' . $match['point'] . ',' . $match['stop1'] . ',' . $match['stop2'] . ')';
						$value[] = '-o-linear-gradient(' . $match['point'] . ',' . $match['stop1'] . ',' . $match['stop2'] . ')';
						
						if ($match['point'] == 'top' || $match['point'] == 'left') {
							if ($match['point'] == 'left') {
								$points = 'left top,right top';
							}
							if ($match['point'] == 'top') {
								$points = 'left top,left bottom';
							}
							$value[] = '-webkit-gradient(linear,' . $points . ',from(' . $match['stop1'] . '),to(' . $match['stop2'] . '))';
						}

						// MS Filter: http://msdn.microsoft.com/en-us/library/ms532997(VS.85,loband).aspx
						$filterType = 0;
						if ($match['point'] == 'left') {
							$filterType = 1;
						}
						$properties['filter'] = $this
								->_getFilterProperty('progid:DXImageTransform.Microsoft.gradient',
										'GradientType=' . $filterType . ',startColorstr=' . $match['stop1'] . ',endColorstr=' . $match['stop2'],
										$properties);
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
			}

			$properties[$property] = $value;
		}
		return $properties;
	}

	/**
	 * Return a MS-filter property
	 *
	 * @param string $name Filter-name
	 * @param string $value Filter-value
	 * @param array $properties OPTIONAL Existing properties to use
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
}
