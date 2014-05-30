<?php

class CM_FormField_File extends CM_FormField_Abstract {

    public function initialize() {
        $this->_options['cardinality'] = $this->_params->getInt('cardinality', 1);
        $this->_options['allowedExtensions'] = $this->_getAllowedExtensions();
        parent::initialize();
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

        $files = array();
        foreach ($userInput as $file) {
            $files[] = new CM_File_UserContent_Temp($file);
        }

        return (array) $files;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('text', $renderParams->getString('text', ''));
        $viewResponse->set('skipDropZone', $renderParams->getBoolean('skipDropZone', false));
    }

    /**
     * @return array List of allowed extension (empty = all)
     */
    protected function _getAllowedExtensions() {
        return array();
    }
}
