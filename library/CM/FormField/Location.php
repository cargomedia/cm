<?php

class CM_FormField_Location extends CM_FormField_SuggestOne {

    /**
     * @param int|null    $minLevel
     * @param int|null    $maxLevel
     * @param string|null $fieldNameDistance
     */
    public function __construct($minLevel = null, $maxLevel = null, $fieldNameDistance = null) {
        parent::__construct();

        if (is_null($minLevel)) {
            $minLevel = CM_Model_Location::LEVEL_COUNTRY;
        }
        if (is_null($maxLevel)) {
            $maxLevel = CM_Model_Location::LEVEL_ZIP;
        }
        $this->_options['levelMin'] = (int) $minLevel;
        $this->_options['levelMax'] = (int) $maxLevel;
        if ($fieldNameDistance) {
            $this->_options['distanceName'] = $fieldNameDistance;
            $this->_options['distanceLevelMin'] = CM_Model_Location::LEVEL_CITY;
        }
    }

    public function getSuggestion($location, CM_Render $render) {
        $names = array();
        for ($level = $location->getLevel(); $level >= CM_Model_Location::LEVEL_COUNTRY; $level--) {
            $names[] = $location->getName($level);
        }
        return array(
            'id'   => $location->getLevel() . '.' . $location->getId(),
            'name' => implode(', ', array_filter($names)),
            'img'  => $render->getUrlResource('layout',
                    'img/flags/' . strtolower($location->getAbbreviation(CM_Model_Location::LEVEL_COUNTRY)) . '.png'),
        );
    }

    /**
     * @param string               $userInput
     * @param CM_Response_Abstract $response
     * @throws CM_Exception_FormFieldValidation
     * @return CM_Model_Location
     */
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
        $location = $this->_getRequestLocationByRequest($request);
        $location = $this->_squashLocationInConstraints($location);
        if ($location) {
            $this->setValue($location);
        }
    }

    /**
     * @param CM_Request_Abstract $request
     * @return CM_Model_Location|null
     */
    protected function _getRequestLocationByRequest(CM_Request_Abstract $request) {
        $ip = $request->getIp();
        if (null === $ip) {
            return null;
        }

        return CM_Model_Location::findByIp($ip);
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

    /**
     * @param CM_Model_Location $location
     * @return CM_Model_Location|null
     */
    private function _squashLocationInConstraints(CM_Model_Location $location = null) {
        if (null === $location) {
            return null;
        }

        if ($location->getLevel() < $this->_options['levelMin']) {
            return null;
        }

        if ($location->getLevel() > $this->_options['levelMax']) {
            $location = $location->get($this->_options['levelMax']);
        }

        return $location;
    }

    public function ajax_getSuggestionByCoordinates(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_View_Ajax $response) {
        $lat = $params->getFloat('lat');
        $lon = $params->getFloat('lon');
        $levelMin = $params->getInt('levelMin');
        $levelMax = $params->getInt('levelMax');

        /** @var CM_FormField_Location $field */
        $field = new static($levelMin, $levelMax);

        $location = CM_Model_Location::findByCoordinates($lat, $lon);
        $location = $field->_squashLocationInConstraints($location);

        if (!$location) {
            throw new CM_Exception('Cannot find a location by coordinates `' . $lat . '` / `' . $lon . '`.');
        }

        return $field->getSuggestion($location, $response->getRender());
    }
}
