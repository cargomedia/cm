<?php

class CM_FormField_File extends CM_FormField_Abstract {

    /**
     * @return array List of allowed extension (empty = all)
     */
    protected function _getAllowedExtensions() {
        return array();
    }

    /**
     * @param CM_File $file Uploaded file
     * @throws CM_Exception If invalid file
     */
    public function validateFile(CM_File $file) {
    }

    /**
     * @param array                $userInput
     * @param CM_Response_Abstract $response
     * @throws CM_Exception_Invalid
     * @return array
     */
    public function validate($userInput, CM_Response_Abstract $response) {
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

    public function prepare(CM_Params $renderParams, CM_ViewResponse $viewResponse) {
        $this->setTplParam('text', $renderParams->getString('text', ''));
        $this->setTplParam('skipDropZone', $renderParams->getBoolean('skipDropZone', false));
    }

    protected function _setup() {
        $this->_options['cardinality'] = $this->_params->getInt('cardinality', 1);
        $this->_options['allowedExtensions'] = $this->_getAllowedExtensions();
    }
}
