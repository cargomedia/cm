<?php

class CM_FormField_GeoPoint extends CM_FormField_Abstract {

    /**
     * @param CM_Frontend_Environment $environment
     * @param array                   $userInput
     * @throws CM_Exception_FormFieldValidation
     * @return CM_Geo_Point
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (!isset($userInput['latitude']) || !is_numeric($userInput['latitude'])) {
            throw new CM_Exception_FormFieldValidation('Latitude needs to be numeric');
        }
        if (!isset($userInput['longitude']) || !is_numeric($userInput['longitude'])) {
            throw new CM_Exception_FormFieldValidation('Longitude needs to be numeric');
        }

        try {
            $point = new CM_Geo_Point($userInput['latitude'], $userInput['longitude']);
        } catch (CM_Exception_Invalid $e) {
            throw new CM_Exception_FormFieldValidation('Invalid latitude or longitude value');
        }

        return $point;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_ViewResponse $viewResponse) {
        /** @var CM_Geo_Point $value */
        $value = $this->getValue();
        $latitude = $value ? $value->getLatitude() : null;
        $longitude = $value ? $value->getLongitude() : null;

        $viewResponse->set('latitude', $latitude);
        $viewResponse->set('longitude', $longitude);
    }

    public function isEmpty($userInput) {
        return empty($userInput['latitude']) || empty($userInput['longitude']);
    }
}
