<?php

class CM_FormField_Location extends CM_FormField_SuggestOne {

	/**
	 * @param string $name OPTIONAL
	 * @param int $minLevel OPTIONAL
	 * @param CM_FormField_Distance $distance OPTIONAL
	 */
	public function __construct($name = 'location', $minLevel = CM_Location::LEVEL_COUNTRY, CM_FormField_Distance $distance = null) {
		parent::__construct($name);
		$this->_options['levelMin'] = (int) $minLevel;
		if ($distance) {
			$this->_options['distanceName'] = $distance->getName();
			$this->_options['distanceLevelMin'] = CM_Location::LEVEL_CITY;
		}
	}

	protected static function _getSuggestion($location) {
		$names = array();
		for ($level = $location->getLevel(); $level >= CM_Location::LEVEL_COUNTRY; $level--) {
			$names[] = $location->getName($level);
		}
		return array('id' => $location->getLevel() . '.' . $location->getId(), 'name' => implode(', ', array_filter($names)),
				'img' => URL_STATIC . 'img/flags/' . strtolower($location->getAbbreviation(CM_Location::LEVEL_COUNTRY)) . '.png');
	}

	protected static function _getSuggestions($term, array $options) {
		$requestLocation = CM_Location::findByIp(CM_Request_Abstract::getIp());
		$locations = new CM_Paging_Location_Suggestions($term, $options['levelMin'], $requestLocation);
		$locations->setPage(1, 15);
		$out = array();
		foreach ($locations as $location) {
			$out[] = self::_getSuggestion($location);
		}
		return $out;
	}

	public function validate($userInput) {
		$value = parent::validate($userInput);
		list($level, $id) = explode('.', $value);
		if ($level < $this->_options['levelMin']) {
			throw new CM_FormFieldValidationException('Invalid location level.');
		}
		return new CM_Location($level, $id);
	}
}
