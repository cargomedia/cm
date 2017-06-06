<?php

class CM_FormField_Text extends CM_FormField_Abstract {

    protected function _initialize() {
        $this->_options['lengthMin'] = $this->_params->has('lengthMin') ? $this->_params->getInt('lengthMin') : null;
        $this->_options['lengthMax'] = $this->_params->has('lengthMax') ? $this->_params->getInt('lengthMax') : null;
        $this->_options['forbidBadwords'] = $this->_params->getBoolean('forbidBadwords', false);
        parent::_initialize();
    }

    public function filterInput($userInput) {
        $userInput = (string) $userInput;
        return mb_convert_encoding($userInput, 'UTF-8', 'UTF-8');
    }

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        if (isset($this->_options['lengthMax']) && mb_strlen($userInput) > $this->_options['lengthMax']) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Too long'));
        }
        if (isset($this->_options['lengthMin']) && mb_strlen($userInput) < $this->_options['lengthMin']) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Too short'));
        }
        if (!empty($this->_options['forbidBadwords'])) {
            $badwordFilter = new CM_Usertext_Filter_Badwords();
            if ($badword = $badwordFilter->getMatch($userInput)) {
                throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('The word `{$badword}` is not allowed', ['badword' => $badword]));
            }
        }
        return $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('autocorrect', $renderParams->has('autocorrect') ? $renderParams->getString('autocorrect') : null);
        $viewResponse->set('autocapitalize', $renderParams->has('autocapitalize') ? $renderParams->getString('autocapitalize') : null);
        $viewResponse->set('autocomplete', $renderParams->has('autocomplete') ? $renderParams->getString('autocomplete') : null);
        $viewResponse->set('tabindex', $renderParams->has('tabindex') ? $renderParams->getInt('tabindex') : null);
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
        $viewResponse->set('placeholder', $renderParams->has('placeholder') ? $renderParams->getString('placeholder') : null);
    }
}
