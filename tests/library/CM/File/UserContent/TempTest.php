<?php

class CM_File_UserContent_TempTest extends CMTest_TestCase {

    public function testConstructorInvalid() {
        try {
            new CM_File_UserContent_Temp(uniqid());
            $this->fail('should throw an exception because id does not exist');
        } catch (CM_Exception_Nonexistent $e) {
            $this->assertTrue(true);
        }
    }

    public function testCreate() {
        $file = CM_File_UserContent_Temp::create('foo.txt');
        $this->assertInstanceOf('CM_File_UserContent_Temp', $file);
        $this->assertSame('foo.txt', $file->getFilenameLabel());
        $this->assertSame('txt', $file->getExtension());
        $this->assertInternalType('string', $file->getUniqid());
    }

    public function testCreateLongName() {
        $file = CM_File_UserContent_Temp::create(str_repeat('a', 500) . '.txt');
        $this->assertSame(str_repeat('a', 96) . '.txt', $file->getFilenameLabel());
    }

    public function testCreateNoExtension() {
        $file = CM_File_UserContent_Temp::create('foo');
        $this->assertNull($file->getExtension());
    }

    public function testGetPathRelative() {
        $file = CM_File_UserContent_Temp::create('foo.txt', 'hello');
        $this->assertStringEndsWith('/' . $file->getUniqid() . '.txt', $file->getPathRelative());
    }

    public function testCreateContent() {
        $file = CM_File_UserContent_Temp::create('foo.txt', 'bar');
        $this->assertSame('bar', $file->read());
    }

    public function testConstruct() {
        $file = CM_File_UserContent_Temp::create('foo.txt');
        $file2 = new CM_File_UserContent_Temp($file->getUniqid());
        $this->assertEquals($file2, $file);
    }

    public function testDelete() {
        $file = CM_File_UserContent_Temp::create('foo.txt', 'bar');
        $this->assertTrue($file->exists());

        $file->delete();
        $this->assertFalse($file->exists());
        try {
            new CM_File_UserContent_Temp($file->getUniqid());
            $this->fail('Can instantiate deleted temp file');
        } catch (CM_Exception_Nonexistent $e) {
            $this->assertTrue(true);
        }
    }

    public function testDeleteOlder() {
        $file = CM_File_UserContent_Temp::create('foo.txt', 'bar');
        $this->assertTrue($file->exists());

        CM_File_UserContent_Temp::deleteOlder(100);
        $this->assertTrue($file->exists());

        CMTest_TH::timeForward(1000);
        CM_File_UserContent_Temp::deleteOlder(100);
        $this->assertFalse($file->exists());
    }
}
