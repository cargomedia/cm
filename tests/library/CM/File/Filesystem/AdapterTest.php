<?php

class CM_File_Filesystem_AdapterTest extends CMTest_TestCase {

    public function testGetAbsolutePath() {
        $adapter = $this->getMockBuilder('CM_File_Filesystem_Adapter')
            ->setConstructorArgs(array('/base/base2'))->getMockForAbstractClass();
        /** @var CM_File_Filesystem_Adapter $adapter */

        $method = CMTest_TH::getProtectedMethod('CM_File_Filesystem_Adapter', '_getAbsolutePath');
        $this->assertSame('/base/base2/bar', $method->invoke($adapter, 'bar'));
        $this->assertSame('/base/base2/bar', $method->invoke($adapter, 'bar/'));
        $this->assertSame('/base/base2/foo', $method->invoke($adapter, '/bar/../foo'));
        $this->assertSame('/base/base2/foo', $method->invoke($adapter, 'bar/../foo'));
        $this->assertSame('/base/base2', $method->invoke($adapter, '/'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Path is out of filesystem directory
     */
    public function testGetAbsolutePathOutOfFilesystem() {
        $adapter = $this->getMockBuilder('CM_File_Filesystem_Adapter')
            ->setConstructorArgs(array('/base/base2'))->getMockForAbstractClass();
        /** @var CM_File_Filesystem_Adapter $adapter */

        $method = CMTest_TH::getProtectedMethod('CM_File_Filesystem_Adapter', '_getAbsolutePath');
        $method->invoke($adapter, '/..');
    }

    public function testGetRelativePath() {
        $adapter = $this->getMockBuilder('CM_File_Filesystem_Adapter')
            ->setConstructorArgs(array('/base/base2'))->getMockForAbstractClass();
        /** @var CM_File_Filesystem_Adapter $adapter */

        $method = CMTest_TH::getProtectedMethod('CM_File_Filesystem_Adapter', '_getRelativePath');
        $this->assertSame('', $method->invoke($adapter, '/base/base2'));
        $this->assertSame('', $method->invoke($adapter, '/base/base2/'));
        $this->assertSame('foo', $method->invoke($adapter, '/base/base2/foo'));
        $this->assertSame('bar', $method->invoke($adapter, '/base/base2/foo/../bar'));
        $this->assertSame('foo/bar', $method->invoke($adapter, '/base/base2/foo/bar/'));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Path is out of filesystem directory
     */
    public function testGetRelativePathOutOfFilesystem() {
        $adapter = $this->getMockBuilder('CM_File_Filesystem_Adapter')
            ->setConstructorArgs(array('/base/base2'))->getMockForAbstractClass();
        /** @var CM_File_Filesystem_Adapter $adapter */

        $method = CMTest_TH::getProtectedMethod('CM_File_Filesystem_Adapter', '_getRelativePath');
        $method->invoke($adapter, '/base/base2/../foo');
    }
}
