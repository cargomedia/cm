<?php

class CM_FormField_FileImage extends CM_FormField_File {

    /** @var int|null */
    protected $_minWidth, $_minHeight, $_maxWidth, $_maxHeight;

    public function __construct($params = null) {
        parent::__construct($params);
        $this->_minWidth = $this->_params->has('minWidth') ? $this->_params->getInt('minWidth') : null;
        $this->_minHeight = $this->_params->has('minHeight') ? $this->_params->getInt('minHeight') : null;
        $this->_maxWidth = $this->_params->has('maxWidth') ? $this->_params->getInt('maxWidth') : null;
        $this->_maxHeight = $this->_params->has('maxHeight') ? $this->_params->getInt('maxHeight') : null;
    }

    public function validateFile(CM_File $file) {
        parent::validateFile($file);

        try {
            $image = new CM_Image_Image($file->read());
            $image->validate();
        } catch (CM_Exception $e) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid image'));
        }

        if (null !== $this->_minWidth && $image->getWidth() < $this->_minWidth) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Image is too small (min width {$minWidth}px).', ['minWidth' => $this->_minWidth]));
        }
        if (null !== $this->_minHeight && $image->getHeight() < $this->_minHeight) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Image is too small (min height {$minHeight}px).', ['minHeight' => $this->_minHeight]));
        }
        if (null !== $this->_maxWidth && $image->getWidth() > $this->_maxWidth) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Maximum resolution exceeded (max width {$maxWidth}px).', ['maxWidth' => $this->_maxWidth]));
        }
        if (null !== $this->_maxHeight && $image->getHeight() > $this->_maxHeight) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Maximum resolution exceeded (max height {$maxHeight}px).', ['maxHeight' => $this->_maxHeight]));
        }
    }

    protected function _getAllowedExtensions() {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }
}
