<?php

class CM_FormField_Location extends CM_FormField_SuggestOne {

    protected function _initialize() {
        $this->_options['levelMin'] = $this->_params->getInt('levelMin', CM_Model_Location::LEVEL_COUNTRY);
        $this->_options['levelMax'] = $this->_params->getInt('levelMax', CM_Model_Location::LEVEL_ZIP);
        if ($this->_params->has('fieldNameDistance') && $this->_params->get('fieldNameDistance')) {
            $this->_options['distanceName'] = $this->_params->getString('fieldNameDistance');
            $this->_options['distanceLevelMin'] = CM_Model_Location::LEVEL_CITY;
        }
        if ($this->_params->has('scopeLocation') && $this->_params->get('scopeLocation')) {
            $this->_options['scopeLocation'] = $this->_params->get('scopeLocation');
        }
        parent::_initialize();
    }

    public function getSuggestion($location, CM_Frontend_Render $render) {
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
     * @param CM_Frontend_Environment $environment
     * @param string                  $userInput
     * @throws CM_Exception_FormFieldValidation
     * @return CM_Model_Location
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $value = parent::validate($environment, $userInput);
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
     * @param CM_Frontend_Environment $environment
     */
    public function setValueByEnvironment(CM_Frontend_Environment $environment) {
        $location = $environment->getLocation();
        $location = $this->_squashLocationInConstraints($location);
        if (null !== $location) {
            $this->setValue($location);
        }
    }

    public function ajax_getSuggestionByCoordinates(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Response_View_Ajax $response) {
        $lat = $params->getFloat('lat');
        $lon = $params->getFloat('lon');
        $location = CM_Model_Location::findByCoordinates($lat, $lon);
        $location = $this->_squashLocationInConstraints($location);

        if (!$location) {
            throw new CM_Exception('Cannot find a location by coordinates `' . $lat . '` / `' . $lon . '`.');
        }

        return $this->getSuggestion($location, $response->getRender());
    }

    protected function _getSuggestions($term, array $options, CM_Frontend_Render $render) {
        $ip = CM_Request_Abstract::getInstance()->getIp();
        $scopeLocation = isset($options['scopeLocation']) ? $options['scopeLocation'] : null;
        $locations = new CM_Paging_Location_Suggestions($term, $options['levelMin'], $options['levelMax'], CM_Model_Location::findByIp($ip), $scopeLocation);
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
}
