<?php

class CM_File_UserContentTest extends CMTest_TestCase {

    public function testGetPathRelative() {
        $file = new CM_File_UserContent('foo', 'my-file.txt', null);
        $this->assertSame('foo/my-file.txt', $file->getPathRelative());

        $file = new CM_File_UserContent('foo', 'my-file.txt', 1);
        $this->assertSame('foo/1/my-file.txt', $file->getPathRelative());

        $file = new CM_File_UserContent('foo', 'my-file.txt', CM_File_UserContent::BUCKETS_COUNT + 2);
        $this->assertSame('foo/2/my-file.txt', $file->getPathRelative());
    }

    public function testGetUrl() {
        $serviceManager = new CM_Service_Manager();
        $filesystemDefault = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local());
        $filesystemFoo = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local());
        $serviceManager->registerInstance('filesystem-usercontent-default', $filesystemDefault);
        $serviceManager->registerInstance('filesystem-usercontent-foo', $filesystemFoo);
        $serviceConfig = array(
            'default' => array(
                'url'        => 'http://example.com/default',
                'filesystem' => 'filesystem-usercontent-default',
            ),
            'foo'     => array(
                'url'        => 'http://example.com/foo',
                'filesystem' => 'filesystem-usercontent-foo',
            ),
        );
        $serviceManager->registerInstance('usercontent', new CM_Service_UserContent($serviceConfig));
        $userFile = new CM_File_UserContent('foo', 'my.jpg', null, $serviceManager);
        $this->assertSame('http://example.com/foo/foo/my.jpg', $userFile->getUrl());

        $userFile = new CM_File_UserContent('bar', 'my.jpg', null, $serviceManager);
        $this->assertSame('http://example.com/default/bar/my.jpg', $userFile->getUrl());
    }
}
