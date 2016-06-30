<?php

class CM_FormField_Geometry_Vector2 extends CM_FormField_Abstract {

    /**
     * @param CM_Frontend_Environment $environment
     * @param array                   $userInput
     * @return CM_FormField_Geometry_Vector2
     * @throws CM_Exception_FormFieldValidation
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $this->_validate($userInput);

        try {
            $vector2 = new CM_Geometry_Vector2($userInput['x'], $userInput['y']);
        } catch (CM_Exception_Invalid $e) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid x or y value'));
        }
        return $vector2;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        /** @var CM_Geometry_Vector2 $value */
        $value = $this->getValue();
        $x = $value ? $value->getX() : null;
        $y = $value ? $value->getY() : null;

        $viewResponse->set('x', $x);
        $viewResponse->set('y', $y);
    }

    public function isEmpty(array $userInput) {
        return parent::isEmpty($userInput['x']) || parent::isEmpty($userInput['y']);
    }

    /**
     * @param array $userInput
     * @throws CM_Exception_FormFieldValidation
     */
    protected function _validate($userInput) {
        if (!isset($userInput['x'])) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('x needs to be numeric'));
        }
        if (!isset($userInput['y'])) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('y needs to be numeric'));
        }
    }
}
