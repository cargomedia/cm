<?php

class CM_FormField_FileImageTest extends CMTest_TestCase {

    public function testValidateFile() {
        $image = new CM_File(DIR_TEST_DATA . 'img/test.jpg');

        $formField = new CM_FormField_FileImage();
        $formField->validateFile($image);

        $this->assertTrue(true);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateFileNoImage() {
        $image = CM_File::createTmp();

        $formField = new CM_FormField_FileImage();
        $formField->validateFile($image);
    }
}
