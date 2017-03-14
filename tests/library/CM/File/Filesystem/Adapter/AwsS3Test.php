<?php

class CM_File_Filesystem_Adapter_AwsS3Test extends CMTest_TestCase {

    public function testRead() {
        $result = new Guzzle\Common\Collection(array('Body' => 'hello'));
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('getObject'))->getMock();
        $clientMock->expects($this->once())->method('getObject')->with(array(
            'ACL'    => 'private',
            'Bucket' => 'bucket',
            'Key'    => 'foo',
        ))->will($this->returnValue($result));
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket');

        $this->assertSame('hello', $adapter->read('foo'));
    }

    public function testWrite() {
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('putObject'))->getMock();
        $clientMock->expects($this->once())->method('putObject')->with(array(
            'Body'        => 'hello',
            'ACL'         => 'public-read',
            'Bucket'      => 'bucket',
            'Key'         => 'bar/foo',
            'ContentType' => 'text/plain',
        ));
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket', 'public-read', '/bar////');

        $adapter->write('foo', 'hello');
    }

    public function testChecksum() {
        $result = new Guzzle\Common\Collection(array('ETag' => '"' . md5('hello') . '"'));
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('headObject'))->getMock();
        $clientMock->expects($this->once())->method('headObject')->with(array(
            'ACL'    => 'private',
            'Bucket' => 'bucket',
            'Key'    => 'foo',
        ))->will($this->returnValue($result));
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket');
        $this->assertSame(md5('hello'), $adapter->getChecksum('foo'));
    }

    public function testExists() {
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('doesObjectExist'))->getMock();
        $clientMock->expects($this->once())->method('doesObjectExist')->with('bucket', 'foo');
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket');

        $adapter->exists('/foo');
    }

    public function testGetModified() {
        $result = new Guzzle\Common\Collection(array('LastModified' => 'Sun, 1 Jan 2006 12:00:00 GMT'));
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('headObject'))->getMock();
        $clientMock->expects($this->once())->method('headObject')->with(array(
            'ACL'    => 'private',
            'Bucket' => 'bucket',
            'Key'    => 'foo',
        ))->will($this->returnValue($result));
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket');

        $this->assertSame(1136116800, $adapter->getModified('/foo'));
    }

    public function testDelete() {
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('deleteObject'))->getMock();
        $clientMock->expects($this->once())->method('deleteObject')->with(array(
            'ACL'    => 'private',
            'Bucket' => 'bucket',
            'Key'    => 'foo',
        ));
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket');

        $adapter->delete('/foo');
    }

    public function testListByPrefix() {
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('getIterator'))->getMock();
        $clientMock->expects($this->once())->method('getIterator')->with('ListObjects',
            array(
                'Bucket' => 'bucket',
                'Prefix' => 'bar/foo/',),
            array(
                'return_prefixes' => true
            ))->will($this->returnValue(
            array(
                array('Key' => 'bar/foo/object1'),
                array('Key' => 'bar/foo/object2'),
                array('Key' => 'bar/foo/subdir/obj1'),
                array('Key' => 'bar/foo/subdir/obj2'),
            )));
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket', 'public-read', '/bar////');

        $this->assertSame(array(
            'files' => array('foo/object1', 'foo/object2', 'foo/subdir/obj1', 'foo/subdir/obj2'),
            'dirs'  => array(),
        ), $adapter->listByPrefix('foo'));
    }

    public function testListByPrefixNoRecursion() {
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('getIterator'))->getMock();
        $clientMock->expects($this->once())->method('getIterator')->with('ListObjects',
            array(
                'Bucket'    => 'bucket',
                'Prefix'    => 'bar/foo/',
                'Delimiter' => '/'),
            array(
                'return_prefixes' => true
            ))->will($this->returnValue(
            array(
                array('Key' => 'bar/foo/object1'),
                array('Key' => 'bar/foo/object2'),
                array('Prefix' => 'bar/foo/subdir/')
            )));
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket', 'public-read', '/bar////');

        $this->assertSame(array(
            'files' => array('foo/object1', 'foo/object2'),
            'dirs'  => array('foo/subdir'),
        ), $adapter->listByPrefix('foo', true));
    }

    public function testCopy() {
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('copyObject'))->getMock();
        $clientMock->expects($this->once())->method('copyObject')->with(array(
            'CopySource' => 'bucket/bar/foo',
            'ACL'        => 'private',
            'Bucket'     => 'bucket',
            'Key'        => 'bar/foobar',
        ));
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket', null, '/bar///');

        $adapter->copy('/foo', '/foobar');
    }

    public function testRename() {
        $adapter = $this->getMockBuilder(CM_File_Filesystem_Adapter_AwsS3::class)->disableOriginalConstructor()
            ->setMethods(array('copy', 'delete'))->getMock();
        $adapter->expects($this->once())->method('copy')->with('/foo', '/foobar');
        $adapter->expects($this->once())->method('delete')->with('/foo');
        /** @var CM_File_Filesystem_Adapter_AwsS3 $adapter */

        $adapter->rename('/foo', '/foobar');
    }

    public function testIsDirectory() {
        $result = new Guzzle\Common\Collection(array('Contents' => array('something')));
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('listObjects'))->getMock();
        $clientMock->expects($this->once())->method('listObjects')->with(array(
            'Bucket'  => 'bucket',
            'Prefix'  => 'foo/',
            'MaxKeys' => 1,
        ))->will($this->returnValue($result));
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket');

        $this->assertTrue($adapter->isDirectory('foo///'));
    }

    public function testGetSize() {
        $result = new Guzzle\Common\Collection(array('ContentLength' => 99));
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('headObject'))->getMock();
        $clientMock->expects($this->once())->method('headObject')->with(array(
            'ACL'    => 'private',
            'Bucket' => 'bucket',
            'Key'    => 'foo',
        ))->will($this->returnValue($result));
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'bucket');

        $this->assertSame(99, $adapter->getSize('/foo'));
    }

    public function testSetup() {
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('doesBucketExist', 'getRegion', 'createBucket'))->getMock();
        $clientMock->expects($this->once())->method('doesBucketExist')->with('my-bucket')->will($this->returnValue(false));
        $clientMock->expects($this->once())->method('getRegion')->will($this->returnValue('my-region'));
        $clientMock->expects($this->once())->method('createBucket')->with(array(
            'Bucket'             => 'my-bucket',
            'LocationConstraint' => 'my-region',
        ));
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'my-bucket');

        $adapter->setup();
    }

    public function testSetupExists() {
        $clientMock = $this->getMockBuilder(Aws\S3\S3Client::class)->disableOriginalConstructor()
            ->setMethods(array('doesBucketExist', 'createBucket'))->getMock();
        $clientMock->expects($this->once())->method('doesBucketExist')->with('my-bucket')->will($this->returnValue(true));
        $clientMock->expects($this->never())->method('createBucket');
        /** @var Aws\S3\S3Client $clientMock */
        $adapter = new CM_File_Filesystem_Adapter_AwsS3($clientMock, 'my-bucket');

        $adapter->setup();
    }
}
