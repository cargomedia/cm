<?php

class CM_FormField_FileImage extends CM_FormField_File {

    public function validateFile(CM_File $file) {
        parent::validateFile($file);

        try {
            $image = new CM_Image_Image($file->read());
            $image->validate();
        } catch (CM_Exception $e) {
            throw new CM_Exception_FormFieldValidation(new CM_I18n_Phrase('Invalid image'));
        }
    }

    protected function _getAllowedExtensions() {
        return array('jpg', 'jpeg', 'gif', 'png');
    }
}
