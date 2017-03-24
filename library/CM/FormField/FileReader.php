<?php

class CM_FormField_FileReader extends CM_FormField_Abstract {

    protected function _initialize() {
        $this->_options['cardinality'] = $this->_params->getInt('cardinality', 1);
        parent::_initialize();
    }

    /**
     * @param CM_File $file Uploaded file
     * @throws CM_Exception If invalid file
     */
    public function validateFile(CM_File $file) {
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param array                   $userInput
     * @throws CM_Exception_Invalid
     * @return array
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = array_filter($userInput, function ($value) {
            return !empty($value);
        });

        if ($this->_options['cardinality'] > 0 && sizeof($userInput) > $this->_options['cardinality']) {
            throw new CM_Exception_Invalid('Too many files uploaded');
        }
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $text = $renderParams->has('text') ? $renderParams->getString('text') : null;
        $buttonTheme = $this->_params->getString('buttonTheme', 'default');
        $skipPreviews = $this->_params->getBoolean('skipPreviews', false);
        $instantUpload = $this->_params->getBoolean('instantUpload', false);

        $viewResponse->set('text', $text);
        $viewResponse->set('buttonTheme', $buttonTheme);

        $viewResponse->getJs()->setProperty('skipPreviews', $skipPreviews);
        $viewResponse->getJs()->setProperty('instantUpload', $instantUpload);
    }
}
