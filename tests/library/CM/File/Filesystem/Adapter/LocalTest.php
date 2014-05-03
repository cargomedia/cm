<?php

class CM_File_Filesystem_Adapter_LocalTest extends CMTest_TestCase {

    /** @var string */
    private $_path;

    protected function setUp() {
        $this->_path = CM_Bootloader::getInstance()->getDirTmp();
    }

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testRead() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->write($this->_path . 'foo', 'hello');
        $this->assertSame('hello', $adapter->read($this->_path . 'foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot read
     */
    public function testReadInvalidpath() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->read($this->_path . 'foo');
    }

    public function testWrite() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->write($this->_path . 'foo', 'hello');
        $this->assertTrue($adapter->exists($this->_path . 'foo'));
        $this->assertSame('hello', $adapter->read($this->_path . 'foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot write
     */
    public function testWriteInvalidPath() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->write('/doesnotexist', 'hello');
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot write
     */
    public function testWriteDirectory() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->ensureDirectory($this->_path . 'foo');
        $adapter->write($this->_path . 'foo', 'hello');
    }

    public function testExists() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $this->assertFalse($adapter->exists($this->_path . 'foo'));

        $adapter->write($this->_path . 'foo', 'hello');
        $this->assertTrue($adapter->exists($this->_path . 'foo'));
    }

    public function testEnsureDirectory() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $this->assertFalse($adapter->isDirectory($this->_path . 'foo'));

        $adapter->ensureDirectory($this->_path . 'foo');
        $this->assertTrue($adapter->isDirectory($this->_path . 'foo'));

        $adapter->ensureDirectory($this->_path . 'foo');
        $this->assertTrue($adapter->isDirectory($this->_path . 'foo'));
    }

    public function testGetModified() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->write($this->_path . 'foo', 'hello');
        $this->assertSameTime((new DateTime())->getTimestamp(), $adapter->getModified($this->_path . 'foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot get modified time
     */
    public function testGetModifiedInvalidPath() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->getModified($this->_path . 'foo');
    }

    public function testDeleteFile() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->delete($this->_path . 'my-file');
        $adapter->write($this->_path . 'my-file', 'hello');
        $this->assertTrue($adapter->exists($this->_path . 'my-file'));

        $adapter->delete($this->_path . 'my-file');
        $this->assertFalse($adapter->exists($this->_path . 'my-file'));
    }

    public function testDeleteDirectory() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->delete($this->_path . 'my-dir');
        $adapter->ensureDirectory($this->_path . 'my-dir');
        $this->assertTrue($adapter->exists($this->_path . 'my-dir'));

        $adapter->delete($this->_path . 'my-dir');
        $this->assertFalse($adapter->exists($this->_path . 'my-dir'));
    }

    public function testRename() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->write($this->_path . 'foo', 'hello');

        $adapter->rename($this->_path . 'foo', $this->_path . 'bar');
        $this->assertFalse($adapter->exists($this->_path . 'foo'));
        $this->assertTrue($adapter->exists($this->_path . 'bar'));
        $this->assertSame('hello', $adapter->read($this->_path . 'bar'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot rename
     */
    public function testRenameInvalidPath() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->rename($this->_path . 'foo', $this->_path . 'bar');
    }

    public function testCopy() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->write($this->_path . 'foo', 'hello');

        $adapter->copy($this->_path . 'foo', $this->_path . 'bar');
        $this->assertTrue($adapter->exists($this->_path . 'foo'));
        $this->assertTrue($adapter->exists($this->_path . 'bar'));
        $this->assertSame('hello', $adapter->read($this->_path . 'bar'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot copy
     */
    public function testCopyInvalidPath() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->copy($this->_path . 'foo', $this->_path . 'bar');
    }

    public function testGetSize() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->write($this->_path . 'foo', 'hello');
        $this->assertSame(5, $adapter->getSize($this->_path . 'foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot get size
     */
    public function testGetSizeInvalidPath() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->getSize($this->_path . 'foo');
    }

    public function testGetChecksum() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->write($this->_path . 'foo', 'hello');
        $this->assertSame(md5('hello'), $adapter->getChecksum($this->_path . 'foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot get md5
     */
    public function testGetChecksumInvalidPath() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $adapter->getChecksum($this->_path . 'foo');
    }
}
