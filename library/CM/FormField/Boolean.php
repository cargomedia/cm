<?php

class CM_FormField_Boolean extends CM_FormField_Abstract {

    const DISPLAY_CHECKBOX = 'checkbox';
    const DISPLAY_SWITCH = 'switch';
    const DISPLAY_BUTTON = 'button';

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        return (bool) $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $display = $renderParams->get('display', self::DISPLAY_CHECKBOX);
        if (!in_array($display, array(self::DISPLAY_CHECKBOX, self::DISPLAY_SWITCH, self::DISPLAY_BUTTON))) {
            throw new CM_Exception_InvalidParam('Display needs to be either `checkbox`, `switch` or `button`.');
        }
        $viewResponse->set('display', $display);
        $viewResponse->set('buttonTheme', $renderParams->get('buttonTheme', 'default'));
        $viewResponse->set('buttonIcon', $renderParams->get('buttonIcon', ''));

        $viewResponse->set('tabindex', $renderParams->has('tabindex') ? $renderParams->getInt('tabindex') : null);
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
        $viewResponse->set('checked', $this->getValue() ? 'checked' : null);
        $viewResponse->set('text', $renderParams->has('text') ? $renderParams->getString('text') : null);
    }
}
