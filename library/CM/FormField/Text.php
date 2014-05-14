<?php

class CM_FormField_Text extends CM_FormField_Abstract {

    public function filterInput($userInput) {
        $userInput = (string) $userInput;
        return mb_convert_encoding($userInput, 'UTF-8', 'UTF-8');
    }

    public function validate($userInput, CM_Response_Abstract $response) {
        if (isset($this->_options['lengthMax']) && mb_strlen($userInput) > $this->_options['lengthMax']) {
            throw new CM_Exception_FormFieldValidation('Too long');
        }
        if (isset($this->_options['lengthMin']) && mb_strlen($userInput) < $this->_options['lengthMin']) {
            throw new CM_Exception_FormFieldValidation('Too short');
        }
        if (!empty($this->_options['forbidBadwords'])) {
            $badwordList = new CM_Paging_ContentList_Badwords();
            if ($badword = $badwordList->getMatch($userInput)) {
                throw new CM_Exception_FormFieldValidation('The word `{$badword}` is not allowed', array('badword' => $badword));
            }
        }
        return $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('autocorrect', $renderParams->has('autocorrect') ? $renderParams->getString('autocorrect') : null);
        $viewResponse->set('autocapitalize', $renderParams->has('autocapitalize') ? $renderParams->getString('autocapitalize') : null);
        $viewResponse->set('tabindex', $renderParams->has('tabindex') ? $renderParams->getInt('tabindex') : null);
        $viewResponse->set('class', $renderParams->has('class') ? $renderParams->getString('class') : null);
        $viewResponse->set('placeholder', $renderParams->has('placeholder') ? $renderParams->getString('placeholder') : null);
    }

    protected function _setup() {
        $this->_options['lengthMin'] = $this->_params->has('lengthMin') ? $this->_params->get('lengthMin') : null;
        $this->_options['lengthMax'] = $this->_params->has('lengthMax') ? $this->_params->get('lengthMax') : null;
        $this->_options['forbidBadwords'] = $this->_params->getBoolean('forbidBadwords', false);
    }
}
