<?php

class CM_Service_UserContentTest extends CMTest_TestCase {

    /** @var CM_Service_UserContent */
    private $_service;

    /** @var CM_File_Filesystem */
    private $_filesystemDefault;

    /** @var CM_File_Filesystem */
    private $_filesystemFoo;

    protected function setUp() {
        $serviceManager = new CM_Service_Manager();
        $this->_filesystemDefault = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local());
        $this->_filesystemFoo = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local());
        $serviceManager->registerInstance('filesystem-usercontent-default', $this->_filesystemDefault);
        $serviceManager->registerInstance('filesystem-usercontent-foo', $this->_filesystemFoo);

        $config = array(
            'default' => array(
                'url'        => 'http://example.com/default',
                'filesystem' => 'filesystem-usercontent-default',
            ),
            'foo'     => array(
                'url'        => 'http://example.com/foo',
                'filesystem' => 'filesystem-usercontent-foo',
            ),
        );
        $this->_service = new CM_Service_UserContent($config);
        $this->_service->setServiceManager($serviceManager);
    }

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetUrlList() {
        $this->assertSame(array(
            'default' => 'http://example.com/default',
            'foo'     => 'http://example.com/foo',
        ), $this->_service->getUrlList());
    }

    public function testGetFilesystemList() {
        $this->assertSame(array(
            'default' => $this->_filesystemDefault,
            'foo'     => $this->_filesystemFoo,
        ), $this->_service->getFilesystemList());
    }

    public function testGetUrl() {
        $this->assertSame('http://example.com/default', $this->_service->getUrl('default'));
        $this->assertSame('http://example.com/default', $this->_service->getUrl('something'));
        $this->assertSame('http://example.com/foo', $this->_service->getUrl('foo'));
    }

    public function testGetFilesystem() {
        $this->assertSame($this->_filesystemDefault, $this->_service->getFilesystem('default'));
        $this->assertSame($this->_filesystemDefault, $this->_service->getFilesystem('something'));
        $this->assertSame($this->_filesystemFoo, $this->_service->getFilesystem('foo'));
    }
}
