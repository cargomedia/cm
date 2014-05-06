<?php

class CM_File_Filesystem_Adapter_LocalTest extends CMTest_TestCase {

    /** @var CM_File_Filesystem_Adapter_Local */
    private $_adapter;

    protected function setUp() {
        $dir = CM_File::createTmpDir();
        $this->_adapter = new CM_File_Filesystem_Adapter_Local($dir->getPath());
    }

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstructDefaultPrefix() {
        $file = CM_File::createTmp(null, 'hello');
        $adapter = new CM_File_Filesystem_Adapter_Local();

        $this->assertSame('/', $adapter->getPathPrefix());
        $this->assertSame('hello', $adapter->read($file->getPath()));
    }

    public function testRead() {
        $this->_adapter->write('foo', 'hello');
        $this->assertSame('hello', $this->_adapter->read('foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot read
     */
    public function testReadInvalidpath() {
        $this->_adapter->read('foo');
    }

    public function testWrite() {
        $this->_adapter->write('foo', 'hello');
        $this->assertTrue($this->_adapter->exists('foo'));
        $this->assertSame('hello', $this->_adapter->read('foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot write
     */
    public function testWriteInvalidPath() {
        $this->_adapter->write('/doesnotexist/foo', 'hello');
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot write
     */
    public function testWriteDirectory() {
        $this->_adapter->ensureDirectory('foo');
        $this->_adapter->write('foo', 'hello');
    }

    public function testExists() {
        $this->assertFalse($this->_adapter->exists('foo'));

        $this->_adapter->write('foo', 'hello');
        $this->assertTrue($this->_adapter->exists('foo'));
    }

    public function testEnsureDirectory() {
        $this->assertFalse($this->_adapter->isDirectory('foo'));

        $this->_adapter->ensureDirectory('foo');
        $this->assertTrue($this->_adapter->isDirectory('foo'));

        $this->_adapter->ensureDirectory('foo');
        $this->assertTrue($this->_adapter->isDirectory('foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Path exists but is not a directory
     */
    public function testEnsureDirectoryExistsFile() {
        $this->_adapter->write('foo', 'hello');
        $this->_adapter->ensureDirectory('foo');
    }

    public function testGetModified() {
        $this->_adapter->write('foo', 'hello');
        $this->assertSameTime(filemtime($this->_adapter->getPathPrefix() . '/foo'), $this->_adapter->getModified('foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot get modified time
     */
    public function testGetModifiedInvalidPath() {
        $this->_adapter->getModified('foo');
    }

    public function testDeleteFile() {
        $this->_adapter->delete('my-file');
        $this->_adapter->write('my-file', 'hello');
        $this->assertTrue($this->_adapter->exists('my-file'));

        $this->_adapter->delete('my-file');
        $this->assertFalse($this->_adapter->exists('my-file'));
    }

    public function testDeleteDirectory() {
        $this->_adapter->delete('my-dir');
        $this->_adapter->ensureDirectory('my-dir');
        $this->assertTrue($this->_adapter->exists('my-dir'));

        $this->_adapter->delete('my-dir');
        $this->assertFalse($this->_adapter->exists('my-dir'));
    }

    public function testRename() {
        $this->_adapter->write('foo', 'hello');

        $this->_adapter->rename('foo', 'bar');
        $this->assertFalse($this->_adapter->exists('foo'));
        $this->assertTrue($this->_adapter->exists('bar'));
        $this->assertSame('hello', $this->_adapter->read('bar'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot rename
     */
    public function testRenameInvalidPath() {
        $this->_adapter->rename('foo', 'bar');
    }

    public function testCopy() {
        $this->_adapter->write('foo', 'hello');

        $this->_adapter->copy('foo', 'bar');
        $this->assertTrue($this->_adapter->exists('foo'));
        $this->assertTrue($this->_adapter->exists('bar'));
        $this->assertSame('hello', $this->_adapter->read('bar'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot copy
     */
    public function testCopyInvalidPath() {
        $this->_adapter->copy('foo', 'bar');
    }

    public function testGetSize() {
        $this->_adapter->write('foo', 'hello');
        $this->assertSame(5, $this->_adapter->getSize('foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot get size
     */
    public function testGetSizeInvalidPath() {
        $this->_adapter->getSize('foo');
    }

    public function testGetChecksum() {
        $this->_adapter->write('foo', 'hello');
        $this->assertSame(md5('hello'), $this->_adapter->getChecksum('foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot get md5
     */
    public function testGetChecksumInvalidPath() {
        $this->_adapter->getChecksum('foo');
    }

    public function testListByPrefix() {
        $filesystem = new CM_File_Filesystem($this->_adapter);

        $pathList = array(
            'foo/foobar/bar',
            'foo/bar2',
            'foo/bar',
        );
        foreach ($pathList as $path) {
            $file = new CM_File($path, $filesystem);
            $file->ensureParentDirectory();
            $file->write('hello');
        }

        $this->assertSame(array(
            'files' => array(
                'foo/foobar/bar',
                'foo/bar',
                'foo/bar2',
            ),
            'dirs'  => array(
                'foo/foobar',
                'foo',
            ),
        ), $this->_adapter->listByPrefix(''));

        $this->assertSame(array(
            'files' => array(
                'foo/foobar/bar',
                'foo/bar',
                'foo/bar2',
            ),
            'dirs'  => array(
                'foo/foobar',
            ),
        ), $this->_adapter->listByPrefix('/foo'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot scan directory
     */
    public function testListByPrefixInvalid() {
        $this->_adapter->listByPrefix('nonexistent');
    }
}
