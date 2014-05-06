<?php

class CM_File_FilesystemTest extends CMTest_TestCase {

    /** @var CM_File_Filesystem */
    private $_filesystem;

    /** @var string */
    private $_path;

    protected function setUp() {
        $adapter = new CM_File_Filesystem_Adapter_Local();
        $this->_filesystem = new CM_File_Filesystem($adapter);
        $this->_path = CM_Bootloader::getInstance()->getDirTmp();
    }

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testDeleteByPrefix() {
        $pathList = array(
            'foo/foobar/bar',
            'foo/bar2',
            'foo/bar',
        );
        /** @var CM_File[] $fileList */
        $fileList = array();
        foreach ($pathList as $path) {
            $file = new CM_File($this->_path . $path, $this->_filesystem);
            $file->ensureParentDirectory();
            $file->write('hello');
            $fileList[] = $file;
            $fileList[] = $file->getParentDirectory();
        }

        foreach ($fileList as $file) {
            $this->assertTrue($file->getExists());
        }

        $this->_filesystem->deleteByPrefix($this->_path);

        foreach ($fileList as $file) {
            $this->assertFalse($file->getExists());
        }
        $dirBase = new CM_File($this->_path);
        $this->assertTrue($dirBase->getExists());
    }
}
