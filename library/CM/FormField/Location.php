<?php

class CM_FormField_Location extends CM_FormField_SuggestOne {

	/**
	 * @param string                     $name
	 * @param int|null                   $minLevel
	 * @param int|null                   $maxLevel
	 * @param CM_FormField_Distance|null $distance
	 */
	public function __construct($name, $minLevel = null, $maxLevel = null, CM_FormField_Distance $distance = null) {
		if (is_null($minLevel)) {
			$minLevel = CM_Model_Location::LEVEL_COUNTRY;
		}
		if (is_null($maxLevel)) {
			$maxLevel = CM_Model_Location::LEVEL_ZIP;
		}
		parent::__construct($name);
		$this->_options['levelMin'] = (int) $minLevel;
		$this->_options['levelMax'] = (int) $maxLevel;
		if ($distance) {
			$this->_options['distanceName'] = $distance->getName();
			$this->_options['distanceLevelMin'] = CM_Model_Location::LEVEL_CITY;
		}
	}

	public function getSuggestion($location, CM_Render $render) {
		$names = array();
		for ($level = $location->getLevel(); $level >= CM_Model_Location::LEVEL_COUNTRY; $level--) {
			$names[] = $location->getName($level);
		}
		return array('id' => $location->getLevel() . '.' . $location->getId(), 'name' => implode(', ', array_filter($names)),
			'img' => $render->getUrlResource('layout', 'img/flags/' . strtolower($location->getAbbreviation(CM_Model_Location::LEVEL_COUNTRY)) . '.png'));
	}

	public function validate($userInput, CM_Response_Abstract $response) {
		$value = parent::validate($userInput, $response);
		if (!preg_match('/^(\d+)\.(\d+)$/', $value, $matches)) {
			throw new CM_Exception_FormFieldValidation('Invalid input format');
		}
		$level = $matches[1];
		$id = $matches[2];
		if ($level < $this->_options['levelMin'] || $level > $this->_options['levelMax']) {
			throw new CM_Exception_FormFieldValidation('Invalid location level.');
		}
		return new CM_Model_Location($level, $id);
	}

	/**
	 * @param CM_Request_Abstract $request
	 */
	public function setValueByRequest(CM_Request_Abstract $request) {
		$ip = $request->getIp();
		$requestLocation = CM_Model_Location::findByIp($ip);
		if (null === $requestLocation) {
			return;
		}

		if ($requestLocation->getLevel() > $this->_options['levelMax']) {
			$requestLocation = $requestLocation->get($this->_options['levelMax']);
			if (null === $requestLocation) {
				return;
			}
		}

		if ($requestLocation->getLevel() <= $this->_options['levelMin']) {
			$requestLocation = $requestLocation->get($this->_options['levelMin']);
			if (null === $requestLocation) {
				return;
			}
		}

		$this->setValue($requestLocation);
	}

	protected function _getSuggestions($term, array $options, CM_Render $render) {
		$ip = CM_Request_Abstract::getInstance()->getIp();
		$locations = new CM_Paging_Location_Suggestions($term, $options['levelMin'], $options['levelMax'], CM_Model_Location::findByIp($ip));
		$locations->setPage(1, 15);
		$out = array();
		foreach ($locations as $location) {
			$out[] = $this->getSuggestion($location, $render);
		}
		return $out;
	}
}
