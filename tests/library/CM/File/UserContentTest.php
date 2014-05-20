<?php

class CM_File_UserContentTest extends CMTest_TestCase {

    /** @var CM_File_Filesystem */
    private $_filesystemDefault;

    /** @var CM_File_Filesystem */
    private $_filesystemFoo;

    protected function setUp() {
        $this->_filesystemDefault = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local());
        $this->_filesystemFoo = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local());
        CM_Service_Manager::getInstance()->registerInstance('filesystem-usercontent-test-default', $this->_filesystemDefault);
        CM_Service_Manager::getInstance()->registerInstance('filesystem-usercontent-test-foo', $this->_filesystemFoo);

        CM_Config::get()->CM_File_UserContent->namespaces = array(
            'default' => array(
                'url'        => 'http://example.com/default',
                'filesystem' => 'filesystem-usercontent-test-default',
            ),
            'foo'     => array(
                'url'        => 'http://example.com/foo',
                'filesystem' => 'filesystem-usercontent-test-foo',
            ),
        );
    }

    protected function tearDown() {
        CMTest_TH::clearEnv();
        CM_Service_Manager::getInstance()->unregister('filesystem-usercontent-test-default');
        CM_Service_Manager::getInstance()->unregister('filesystem-usercontent-test-foo');
    }

    public function testGetPathRelative() {
        $file = new CM_File_UserContent('foo', 'my-file.txt', null);
        $this->assertSame('foo/my-file.txt', $file->getPathRelative());

        $file = new CM_File_UserContent('foo', 'my-file.txt', 1);
        $this->assertSame('foo/1/my-file.txt', $file->getPathRelative());

        $file = new CM_File_UserContent('foo', 'my-file.txt', CM_File_UserContent::BUCKETS_COUNT + 2);
        $this->assertSame('foo/2/my-file.txt', $file->getPathRelative());
    }

    public function testGetUrlList() {
        $this->assertSame(array(
            'default' => 'http://example.com/default',
            'foo'     => 'http://example.com/foo',
        ), CM_File_UserContent::getUrlList());
    }

    public function testGetFilesystemList() {
        $this->assertSame(array(
            'default' => $this->_filesystemDefault,
            'foo'     => $this->_filesystemFoo,
        ), CM_File_UserContent::getFilesystemList());
    }

    public function testGetUrlByNamespace() {
        $this->assertSame('http://example.com/default', CM_File_UserContent::getUrlByNamespace('default'));
        $this->assertSame('http://example.com/default', CM_File_UserContent::getUrlByNamespace('something'));
        $this->assertSame('http://example.com/foo', CM_File_UserContent::getUrlByNamespace('foo'));
    }

    public function testGetFilesystemByNamespace() {
        $this->assertSame($this->_filesystemDefault, CM_File_UserContent::getFilesystemByNamespace('default'));
        $this->assertSame($this->_filesystemDefault, CM_File_UserContent::getFilesystemByNamespace('something'));
        $this->assertSame($this->_filesystemFoo, CM_File_UserContent::getFilesystemByNamespace('foo'));
    }

    public function testGetUrl() {
        $userFile = new CM_File_UserContent('foo', 'my.jpg');
        $this->assertSame('http://example.com/foo/foo/my.jpg', $userFile->getUrl());

        $userFile = new CM_File_UserContent('bar', 'my.jpg');
        $this->assertSame('http://example.com/default/bar/my.jpg', $userFile->getUrl());
    }
}
