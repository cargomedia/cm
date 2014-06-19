<?php

class CM_FormField_FileTest extends CMTest_TestCase {

    /** @var string */
    private $_dir;

    protected function setUp() {
        $this->_dir = CM_Bootloader::getInstance()->getDirTmp();
    }

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testParseUserInput() {
        $file1 = CM_File_UserContent_Temp::create($this->_dir . 'test1', '123');
        $file2 = CM_File_UserContent_Temp::create($this->_dir . 'test1', '123');
        $userInput = array(
            $file1->getFileName(),
            '',
            null,
            $file2->getFileName(),
        );
        $formField = new CM_FormField_File();
        $parsedUserInput = $formField->parseUserInput($userInput);
        $this->assertCount(2, $parsedUserInput);

        $this->assertContains($file1, $parsedUserInput);
        $this->assertContains($file2, $parsedUserInput);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testParseUserInputInvalid() {
        $formField = new CM_FormField_File();
        $formField->parseUserInput('foo');
    }
}
