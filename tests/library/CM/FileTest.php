<?php

class CM_FileTest extends CMTest_TestCase {

    protected static $_backupContent;

    protected $_testFilePath = '';

    public function setUp() {
        $this->_testFilePath = DIR_TEST_DATA . 'img/test.jpg';
        self::$_backupContent = file_get_contents($this->_testFilePath);
    }

    public function tearDown() {
        file_put_contents($this->_testFilePath, self::$_backupContent);
        CMTest_TH::clearEnv();
    }

    public function testConstruct() {
        $file = new CM_File($this->_testFilePath);

        $this->assertEquals($this->_testFilePath, $file->getPath());
        $this->assertEquals('image/jpeg', $file->getMimeType());
        $this->assertEquals('jpg', $file->getExtension());
        $this->assertEquals('37b1b8cb44ed126b0cd2fa25565b844b', $file->getHash());
        $this->assertEquals('test.jpg', $file->getFileName());
        $this->assertEquals(filesize($this->_testFilePath), $file->getSize());
        $this->assertEquals(file_get_contents($this->_testFilePath), $file->read());
        $this->assertEquals(file_get_contents($this->_testFilePath), '' . $file);
    }

    public function testConstructNonExistent() {
        $file = new CM_File(DIR_TEST_DATA . '/nonexistent-file');
        $this->assertEquals(DIR_TEST_DATA . '/nonexistent-file', $file->getPath());
    }

    public function testSanitizeFilename() {
        $filename = "~foo@! <}\   b\0a=r.tar.(gz";
        $this->assertSame("foo-bar.tar.gz", CM_File::sanitizeFilename($filename));

        try {
            CM_File::sanitizeFilename('&/&*<');
        } catch (CM_Exception_Invalid $ex) {
            $this->assertContains('Invalid filename.', $ex->getMessage());
        }
    }

    public function testCreate() {
        $path = DIR_TEST_DATA . 'foo';
        $this->assertFileNotExists($path);

        $file = CM_File::create($path);
        $this->assertFileExists($path);
        $this->assertInstanceOf('CM_File', $file);
        $this->assertEquals($path, $file->getPath());
        $this->assertEquals('', $file->read());
        $file->delete();

        $file = CM_File::create($path, 'bar');
        $this->assertEquals('bar', $file->read());
        $file->delete();

        try {
            CM_File::create(DIR_TEST_DATA);
            $this->fail('Could create file with invalid path');
        } catch (CM_Exception $e) {
            $this->assertContains('Cannot write', $e->getMessage());
        }
    }

    public function testCreateTmp() {
        $file = CM_File::createTmp();
        $this->assertFileExists($file->getPath());
        $this->assertNull($file->getExtension());
        $this->assertEmpty($file->read());
        $file->delete();

        $file = CM_File::createTmp('');
        $this->assertSame('', $file->getExtension());
        $file->delete();

        $file = CM_File::createTmp('testExtension', 'bar');
        $this->assertContains('testextension', $file->getExtension());
        $this->assertEquals('bar', $file->read());
        $file->delete();
    }

    public function testDeleteRecursive() {
        $dir = CM_File::createTmpDir();
        $file = new CM_File($dir->getPath() . '/foo');
        $file->write('hello');
        $this->assertTrue($dir->getExists());
        $this->assertTrue($file->getExists());

        $dir->delete(true);
        $this->assertFalse($dir->getExists());
        $this->assertFalse($file->getExists());
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot delete directory
     */
    public function testDeleteNonRecursive() {
        $dir = CM_File::createTmpDir();
        $file = new CM_File($dir->getPath() . '/foo');
        $file->write('hello');
        $this->assertTrue($dir->getExists());
        $this->assertTrue($file->getExists());

        $dir->delete();
    }

    public function testTruncate() {
        $file = new CM_File($this->_testFilePath);
        $file->write('foo');
        $this->assertNotSame('', $file->read());
        $file->truncate();
        $this->assertSame('', $file->read());
    }

    public function testAppend() {
        $file = CM_File::createTmp();
        $file->append('foo');
        $this->assertSame('foo', $file->read());
        $file->append('bar');
        $this->assertSame('foobar', $file->read());
    }

    public function testGetMimeType() {
        $file = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
        $this->assertSame('image/jpeg', $file->getMimeType());
    }

    public function testRead() {
        $file = CM_File::createTmp(null, 'hello');
        $this->assertSame('hello', $file->read());

        $file->write('foo');
        $this->assertSame('foo', $file->read());

        file_put_contents($file->getPath(), 'bar');
        $this->assertSame('foo', $file->read());
    }

    public function testReadFirstLine() {
        $file = CM_File::createTmp(null, 'hello');
        $this->assertSame('hello', $file->readFirstLine());

        $file = CM_File::createTmp(null, "hello\r\nworld\r\nfoo");
        $this->assertSame("hello\r\n", $file->readFirstLine());

        $file = CM_File::createTmp(null, '');
        $this->assertSame('', $file->readFirstLine());
    }

    public function testEnsureParentDirectory() {
        $dir = new CM_File(CM_Bootloader::getInstance()->getDirTmp() . 'foo/bar');
        $file = new CM_File($dir->getPath() . '/mega.txt');
        $this->assertFalse($dir->getExists());

        $file->ensureParentDirectory();
        $this->assertTrue($dir->getExists());
        $this->assertFalse($file->getExists());
    }

    public function createTmpDir() {
        $dir = CM_File::createTmpDir();

        $this->assertTrue($dir->getPath());
        $this->assertTrue($dir->isDirectory());
    }

    public function testJoinPath() {
        $dir = CM_File::createTmpDir();
        $fileJoined = $dir->joinPath('foo', 'bar', '//mega//', 'jo', '..', 'nei');
        $fileJoinedPathRelative = substr($fileJoined->getPath(), strlen($dir->getPath()) + 1);
        $this->assertSame('/foo/bar/mega/nei', $fileJoinedPathRelative);
    }
}
