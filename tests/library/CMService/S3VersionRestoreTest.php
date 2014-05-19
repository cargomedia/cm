<?php

class CMService_S3VersionRestoreTest extends CMTest_TestCase {

    /** @var \Aws\S3\S3Client */
    private $_client;

    /** @var string */
    private $_bucket;

    /** @var CM_File_Filesystem */
    private $_filesystem;

    /** @var CMService_S3VersionRestore */
    private $_restore;

    public function setUp() {
        $config = CM_Config::get();
        if (!isset($config->CMService_S3VersionRestoreTest)) {
            $this->markTestSkipped('Missing config');
        }
        $clientParams = $config->CMService_S3VersionRestoreTest->clientParams;

        $this->_client = \Aws\S3\S3Client::factory($clientParams);
        $this->_bucket = 'test-CMService_S3VersionRestoreTest';
        $this->_filesystem = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_AwsS3($this->_client, $this->_bucket));
        $this->_restore = new CMService_S3VersionRestore($this->_client, $this->_bucket);

        $this->_client->createBucket(array('Bucket' => $this->_bucket));
        $this->_client->putBucketVersioning(array('Bucket' => $this->_bucket, 'Status' => 'Enabled'));
    }

    public function tearDown() {
        $clear = new Aws\S3\Model\ClearBucket($this->_client, $this->_bucket);
        $clear->clear();
        $this->_client->deleteBucket(array('Bucket' => $this->_bucket));
    }

    public function testGetVersioningEnabled() {
        $this->_client->putBucketVersioning(array('Bucket' => $this->_bucket, 'Status' => 'Enabled'));
        $this->assertSame(true, $this->_restore->getVersioningEnabled());
        $this->_client->putBucketVersioning(array('Bucket' => $this->_bucket, 'Status' => 'Suspended'));
        $this->assertSame(false, $this->_restore->getVersioningEnabled());
    }
}
