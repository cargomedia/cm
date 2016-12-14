<?php

class CM_FormField_ColorPalette extends CM_FormField_Set_Select {

    /** @var CM_Color_RGB[] */
    private $_palette;

    protected function _initialize() {
        $palette = $this->_params->getArray('palette');
        foreach ($palette as $color) {
            if (!$color instanceof CM_Color_RGB) {
                throw new CM_Exception_Invalid('Palette contains non-color');
            }
        }
        $this->_palette = $palette;

        parent::_initialize();
    }

    protected function _getOptionList() {
        $optionList = [];
        foreach ($this->_palette as $color) {
            $optionList[$color->getHexString()] = $color->getHexString();
        }
        return $optionList;
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param string                  $userInput
     * @return CM_Color_RGB
     * @throws CM_Exception_FormFieldValidation
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = parent::validate($environment, $userInput);

        try {
            $color = CM_Color_RGB::fromHexString($userInput);
        } catch (CM_Exception_Invalid $ex) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid color'));
        }

        return $color;
    }

}
