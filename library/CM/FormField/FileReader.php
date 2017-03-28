<?php

class CM_FormField_FileReader extends CM_FormField_Abstract {

    protected function _initialize() {
        $this->_options['cardinality'] = $this->_params->getInt('cardinality', 1);
        $this->_options['allowedExtensions'] = $this->_getAllowedExtensions();
        parent::_initialize();
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $text = $renderParams->has('text') ? $renderParams->getString('text') : null;
        $buttonTheme = $this->_params->getString('buttonTheme', 'default');
        $skipPreviews = $this->_params->getBoolean('skipPreviews', false);

        $viewResponse->set('text', $text);
        $viewResponse->set('buttonTheme', $buttonTheme);

        $viewResponse->getJs()->setProperty('skipPreviews', $skipPreviews);
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param array                   $userInput
     * @throws CM_Exception_Invalid
     * @return array
     */
    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $userInput = base64_decode($userInput);
        $files = CM_Util::jsonDecode($userInput);

        $files = array_filter($files, function ($value) {
            return !empty($value);
        });

        if ($this->_options['cardinality'] > 0 && sizeof($files) > $this->_options['cardinality']) {
            throw new CM_Exception_Invalid('Too many files uploaded');
        }

        foreach ($files as &$file) {
            $file = $this->createFile($file);
        }
        return $files;
    }

    /**
     * @param array $fileData
     * @return CM_File
     */
    public function createFile(array $fileData) {
        $fileRaw = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fileData['data']));
        $file = new CM_File(); //todo make possible to create file without path
        return $file;
    }

    /**
     * @return array List of allowed extension (empty = all)
     */
    protected function _getAllowedExtensions() {
        return [];
    }
}
