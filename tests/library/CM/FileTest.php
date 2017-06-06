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
        $this->assertEquals(file_get_contents($this->_testFilePath), '' . $file->read());
    }

    public function testConstructNonExistent() {
        $file = new CM_File(DIR_TEST_DATA . '/nonexistent-file');
        $this->assertEquals(DIR_TEST_DATA . 'nonexistent-file', $file->getPath());
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
        $this->assertTrue($file->exists());
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
        $file = $dir->joinPath('foo');
        $file->write('hello');
        $this->assertTrue($dir->exists());
        $this->assertTrue($file->exists());

        $dir->delete(true);
        $this->assertFalse($dir->exists());
        $this->assertFalse($file->exists());
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot delete directory
     */
    public function testDeleteNonRecursive() {
        $dir = CM_File::createTmpDir();
        $file = $dir->joinPath('foo');
        $file->write('hello');
        $this->assertTrue($dir->exists());
        $this->assertTrue($file->exists());

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

    public function testGetMimeTypeJavascript() {
        $file = CM_File::createTmp('js', 'alert("foo");');
        $this->assertSame('text/javascript', $file->getMimeType());
    }

    public function testFindMimeTypeByExtension(){
        $this->assertSame('text/javascript', CM_File::findMimeTypeByExtension('js'));
        $this->assertSame(null, CM_File::findMimeTypeByExtension('unknown'));
    }

    public function testListFiles() {
        $adapter = $this->mockObject('CM_File_Filesystem_Adapter');
        $fs = new CM_File_Filesystem($adapter);
        $file = new CM_File('foo', $fs);
        $adapter->mockMethod('equals')->set(function (CM_File_FileSystem_Adapter $other) use ($adapter) {
            return $adapter === $other;
        });
        $adapter->mockMethod('listByPrefix')->set(function ($path, $noRecursion) use ($file) {
            $this->assertSame($file->getPath(), $path);
            $this->assertNull($noRecursion);
            return [
                'dirs'  =>
                    [
                        $path . '/foo/',
                        $path . '/bar/',
                    ],
                'files' =>
                    [
                        $path . '/foo/bar',
                        $path . '/bar/foo',
                        $path . '/foo.bar',
                        $path . '/bar.foo',
                    ]
            ];
        });
        $filePath = $file->getPath();
        $expected = [
            new CM_File($filePath . '/foo/', $fs),
            new CM_File($filePath . '/bar/', $fs),
            new CM_File($filePath . '/foo/bar', $fs),
            new CM_File($filePath . '/bar/foo', $fs),
            new CM_File($filePath . '/foo.bar', $fs),
            new CM_File($filePath . '/bar.foo', $fs),
        ];
        $this->assertEquals($expected, $file->listFiles());
    }

    public function testRead() {
        $file = CM_File::createTmp(null, 'hello');
        $this->assertSame('hello', $file->read());

        $file->write('foo');
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
        $this->assertFalse($dir->exists());

        $file->ensureParentDirectory();
        $this->assertTrue($dir->exists());
        $this->assertFalse($file->exists());
    }

    public function createTmpDir() {
        $dir = CM_File::createTmpDir();

        $this->assertTrue($dir->getPath());
        $this->assertTrue($dir->isDirectory());
    }

    public function testJoinPath() {
        $dir = CM_File::createTmpDir();
        $fileJoined = $dir->joinPath('foo', 'bar', '//mega//', 'jo', '..', 'nei');
        $this->assertSame($dir->getPath() . '/foo/bar/mega/nei', $fileJoined->getPath());
    }

    public function testEquals() {
        $filesystem1 = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local('/'));
        $filesystem2 = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local('/'));
        $filesystem3 = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local('/tmp'));

        $this->assertFalse((new CM_File('/foo', $filesystem1))->equals(null));
        $this->assertTrue((new CM_File('/foo', $filesystem1))->equals(new CM_File('/foo', $filesystem1)));
        $this->assertFalse((new CM_File('/foo', $filesystem1))->equals(new CM_File('/bar', $filesystem1)));
        $this->assertTrue((new CM_File('/foo', $filesystem1))->equals(new CM_File('/foo', $filesystem2)));
        $this->assertFalse((new CM_File('/foo', $filesystem1))->equals(new CM_File('/bar', $filesystem2)));
        $this->assertFalse((new CM_File('/foo', $filesystem1))->equals(new CM_File('/foo', $filesystem3)));
        $this->assertFalse((new CM_File('/foo', $filesystem1))->equals(new CM_File('/bar', $filesystem3)));
    }

    public function testCopyToFile() {
        $dirTmp = CM_Bootloader::getInstance()->getDirTmp();
        $filesystem1 = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local($dirTmp));
        $filesystem2 = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local($dirTmp));
        $filesystem3 = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local($dirTmp . 'subdir'));
        $filesystem3->getAdapter()->setup();

        $file1 = new CM_File('foo', $filesystem1);
        $file2 = new CM_File('bar', $filesystem2);
        $file3 = new CM_File('zoo', $filesystem3);

        $file1->write('hello');

        $file1->copyToFile($file2);
        $this->assertSame($file1->read(), $file2->read());

        $file1->copyToFile($file3);
        $this->assertSame($file1->read(), $file3->read());
    }

    public function testGetPathOnLocalFilesystem() {
        $dirTmp = CM_Bootloader::getInstance()->getDirTmp();
        $filesystem = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local($dirTmp));
        $file = new CM_File('foo', $filesystem);

        $this->assertSame(rtrim($dirTmp, '/') . '/foo', $file->getPathOnLocalFilesystem());
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testGetPathOnLocalFilesystemUnexpectedFilesystem() {
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()->getMock();
        $filesystem = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket'));
        $file = new CM_File('foo', $filesystem);

        $file->getPathOnLocalFilesystem();
    }
}
