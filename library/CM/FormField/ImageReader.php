<?php

class CM_FormField_ImageReader extends CM_FormField_FileReader {

    /** @var int */
    protected $_minWidth, $_minHeight, $_maxWidth, $_maxHeight;

    public function __construct($params = null) {
        parent::__construct($params);
        $this->_minWidth = $this->_params->getInt('minWidth', 300);
        $this->_minHeight = $this->_params->getInt('minHeight', 300);
        $this->_maxWidth = $this->_params->getInt('maxWidth', 4096);
        $this->_maxHeight = $this->_params->getInt('maxHeight', 4096);
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        parent::prepare($renderParams, $environment, $viewResponse);

        $viewResponse->getJs()->setProperty('_minWidth', $this->_minWidth);
        $viewResponse->getJs()->setProperty('_minHeight', $this->_minHeight);
        $viewResponse->getJs()->setProperty('_maxWidth', $this->_maxWidth);
        $viewResponse->getJs()->setProperty('_maxHeight', $this->_maxHeight);
    }

    public function createFile(array $fileData) {
        $imageContent = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fileData['data']));
        $file = new CM_Image_Image($imageContent);
        $this->validateFile($file);
        return $file;
    }

    public function validateFile(CM_Image_Image $image) {
        try {
            $image->validate();
        } catch (CM_Exception $e) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid image'));
        }

        $minSize = $this->_getMinSize($image);
        $maxSize = $this->_getMaxSize($image);
        if ($image->getWidth() < $minSize->getX()) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Image is too small (min width {$minWidth}px).', ['minWidth' => $minSize->getX()]));
        }
        if ($image->getHeight() < $minSize->getY()) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Image is too small (min height {$minHeight}px).', ['minHeight' => $minSize->getY()]));
        }
        if ($image->getWidth() > $maxSize->getX()) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Maximum resolution exceeded (max width {$maxWidth}px).', ['maxWidth' => $maxSize->getX()]));
        }
        if ($image->getHeight() > $maxSize->getY()) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Maximum resolution exceeded (max height {$maxHeight}px).', ['maxHeight' => $maxSize->getY()]));
        }
    }

    protected function _getAllowedExtensions() {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }

    /**
     * @param CM_Image_Image $image
     * @return CM_Geometry_Vector2
     */
    protected function _getMinSize(CM_Image_Image $image) {
        return new CM_Geometry_Vector2($this->_minWidth, $this->_minHeight);
    }

    /**
     * @param CM_Image_Image $image
     * @return CM_Geometry_Vector2
     */
    protected function _getMaxSize(CM_Image_Image $image) {
        return new CM_Geometry_Vector2($this->_maxWidth, $this->_maxHeight);
    }
}
