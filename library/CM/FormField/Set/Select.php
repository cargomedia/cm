<?php

class CM_FormField_Set_Select extends CM_FormField_Set {

    const DISPLAY_SELECT = 'select';
    const DISPLAY_RADIOS = 'radios';

    public function validate($userInput, CM_Response_Abstract $response) {
        if (!in_array($userInput, $this->_getValues())) {
            throw new CM_Exception_FormFieldValidation('Invalid value');
        }
        return $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_ViewResponse $viewResponse) {
        if (!isset($renderParams['display'])) {
            $renderParams['display'] = self::DISPLAY_SELECT;
        }
        if ($renderParams['display'] !== self::DISPLAY_SELECT && $renderParams['display'] !== self::DISPLAY_RADIOS) {
            throw new CM_Exception_InvalidParam('Display needs to be either `select` or `radios`');
        }
        $this->setTplParam('display', $renderParams['display']);
        $this->setTplParam('class', !empty($renderParams['class']) ? $renderParams['class'] : null);

        $this->setTplParam('placeholder', !empty($renderParams['placeholder']));
        $this->setTplParam('optionList', $this->_getOptionList());
        $this->setTplParam('labelPrefix', !empty($renderParams['labelPrefix']) ? $renderParams['labelPrefix'] : null);

        $this->setTplParam('translate', !empty($renderParams['translate']) || !empty($renderParams['translatePrefix']));
        $this->setTplParam('translatePrefix', !empty($renderParams['translatePrefix']) ? $renderParams['translatePrefix'] : '');
    }
}
