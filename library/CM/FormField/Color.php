<?php

class CM_FormField_Color extends CM_FormField_Abstract {

    /**
     * @param CM_Frontend_Environment $environment
     * @param string                  $userInput
     * @return CM_Color_RGB
     * @throws CM_Exception_FormFieldValidation
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        try {
            $color = CM_Color_RGB::fromHexString($userInput);
        } catch (CM_Exception_Invalid $ex) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid color'));
        }
        return $color;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        /** @var CM_Color_RGB $value */
        $color = $this->getValue();

        $viewResponse->set('color', $color);
    }

}
