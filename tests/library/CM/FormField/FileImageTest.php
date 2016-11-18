<?php

class CM_FormField_FileImageTest extends CMTest_TestCase {

    public function testValidateFile() {
        $image = new CM_File(DIR_TEST_DATA . 'img/test.jpg');

        $formField = new CM_FormField_FileImage();
        $formField->validateFile($image);

        $this->assertTrue(true);
    }

    public function testValidateFileValidSize() {
        $image = new CM_File(DIR_TEST_DATA . 'img/test.jpg');

        $formField = new CM_FormField_FileImage(['minWidth' => 300, 'minHeight' => 200, 'maxWidth' => 400, 'maxHeight' => 300]);
        $formField->validateFile($image);

        $this->assertTrue(true);
    }

    public function testValidateFileNoImage() {
        $image = CM_File::createTmp();

        $formField = new CM_FormField_FileImage();
        $exception = $this->catchException(function () use ($formField, $image) {
            $formField->validateFile($image);
        });

        $this->assertInstanceOf(CM_Exception_FormFieldValidation::class, $exception);
        $this->assertSame('FormField Validation failed', $exception->getMessage());
        /** @var CM_Exception $exception */
        $this->assertSame('Invalid image', $exception->getMessagePublic(new CM_Frontend_Render()));
    }

    public function testValidateFileInvalidSize() {
        $image = new CM_File(DIR_TEST_DATA . 'img/test.jpg');

        $formField = new CM_FormField_FileImage(['minWidth' => 390, 'minHeight' => 200, 'maxWidth' => 400, 'maxHeight' => 300]);
        $exception = $this->catchException(function () use ($formField, $image) {
            $formField->validateFile($image);
        });

        $this->assertInstanceOf(CM_Exception_FormFieldValidation::class, $exception);
        $this->assertSame('FormField Validation failed', $exception->getMessage());
        /** @var CM_Exception $exception */
        $this->assertSame('Image is too small (min width 390px).', $exception->getMessagePublic(new CM_Frontend_Render()));

        $formField = new CM_FormField_FileImage(['minWidth' => 300, 'minHeight' => 290, 'maxWidth' => 400, 'maxHeight' => 300]);
        $exception = $this->catchException(function () use ($formField, $image) {
            $formField->validateFile($image);
        });

        $this->assertInstanceOf(CM_Exception_FormFieldValidation::class, $exception);
        $this->assertSame('FormField Validation failed', $exception->getMessage());
        /** @var CM_Exception $exception */
        $this->assertSame('Image is too small (min height 290px).', $exception->getMessagePublic(new CM_Frontend_Render()));

        $formField = new CM_FormField_FileImage(['minWidth' => 300, 'minHeight' => 200, 'maxWidth' => 310, 'maxHeight' => 300]);
        $exception = $this->catchException(function () use ($formField, $image) {
            $formField->validateFile($image);
        });

        $this->assertInstanceOf(CM_Exception_FormFieldValidation::class, $exception);
        $this->assertSame('FormField Validation failed', $exception->getMessage());
        /** @var CM_Exception $exception */
        $this->assertSame('Maximum resolution exceeded (max width 310px).', $exception->getMessagePublic(new CM_Frontend_Render()));

        $formField = new CM_FormField_FileImage(['minWidth' => 300, 'minHeight' => 200, 'maxWidth' => 400, 'maxHeight' => 210]);
        $exception = $this->catchException(function () use ($formField, $image) {
            $formField->validateFile($image);
        });

        $this->assertInstanceOf(CM_Exception_FormFieldValidation::class, $exception);
        $this->assertSame('FormField Validation failed', $exception->getMessage());
        /** @var CM_Exception $exception */
        $this->assertSame('Maximum resolution exceeded (max height 210px).', $exception->getMessagePublic(new CM_Frontend_Render()));
    }
}
