<?php

class CM_FormField_ColorPalette extends CM_FormField_Abstract {

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

        $partOfPalette = Functional\some($this->_palette, function (CM_Color_RGB $colorPalette) use ($color) {
            return $color->equals($colorPalette);
        });
        if (!$partOfPalette) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Color not part of palette'));
        }

        return $color;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        /** @var CM_Color_RGB $value */
        $color = $this->getValue();

        $viewResponse->set('color', $color);
        $viewResponse->set('palette', $this->_palette);
    }

}
