<?php

class CM_FormField_Geometry_Vector3 extends CM_FormField_Geometry_Vector2 {

    /**
     * @param CM_Frontend_Environment $environment
     * @param array                   $userInput
     * @return CM_Geometry_Vector3
     * @throws CM_Exception_FormFieldValidation
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $this->_validate($userInput);

        try {
            $vector3 = new CM_Geometry_Vector3($userInput['x'], $userInput['y'], $userInput['z']);
        } catch (CM_Exception_Invalid $e) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid x, y or z value'));
        }

        return $vector3;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        /** @var CM_Geometry_Vector3 $value */
        $value = $this->getValue();

        parent::prepare($renderParams, $environment, $viewResponse);
        $z = $value ? $value->getZ() : null;
        $viewResponse->set('z', $z);
    }

    /**
     * @param array $userInput
     * @return bool
     */
    public function isEmpty($userInput) {
        return parent::isEmpty($userInput) || CM_FormField_Abstract::isEmpty($userInput['z']);
    }

    /**
     * @param array $userInput
     * @throws CM_Exception_FormFieldValidation
     */
    protected function _validate($userInput) {
        parent::_validate($userInput);
        if (!isset($userInput['z'])) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('z needs to be numeric'));
        }
    }
}
