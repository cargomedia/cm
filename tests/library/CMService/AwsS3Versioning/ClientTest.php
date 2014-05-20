<?php

class CMService_AwsS3Versioning_ClientTest extends CMTest_TestCase {

    /** @var \Aws\S3\S3Client */
    private $_client;

    /** @var string */
    private $_bucket;

    /** @var CM_File_Filesystem */
    private $_filesystem;

    /** @var CMService_AwsS3Versioning_Client */
    private $_restore;

    public function setUp() {
        $config = CM_Config::get();
        $className = __CLASS__;
        $key = (string) $config->$className->key;
        $secret = (string) $config->$className->secret;
        $region = (string) $config->$className->region;
        if (empty($key) || empty($secret)) {
            $this->markTestSkipped('Missing `key` or `secret` config.');
        }

        $this->_client = \Aws\S3\S3Client::factory(array('key' => $key, 'secret' => $secret));
        $this->_bucket = strtolower(str_replace('_', '-', 'test-' . __CLASS__));
        $this->_filesystem = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_AwsS3($this->_client, $this->_bucket));
        $this->_restore = new CMService_AwsS3Versioning_Client($this->_client, $this->_bucket);

        $this->_client->createBucket(array('Bucket' => $this->_bucket, 'LocationConstraint' => $region));
        $this->_client->waitUntilBucketExists(array('Bucket' => $this->_bucket));
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

    public function testGetVersions() {
        $this->_filesystem->write('bar', 'mega1');
        $this->_filesystem->write('bar', 'mega2');
        $this->_filesystem->delete('bar');

        $versions = $this->_restore->getVersions('bar');
        $this->assertCount(3, $versions);
        $lastModified = null;
        foreach ($versions as $version) {
            $this->assertInstanceOf('CMService_AwsS3Versioning_Response_Version', $version);
            $this->assertSame('bar', $version->getKey());
            if (null !== $lastModified) {
                $this->assertTrue($lastModified >= $version->getLastModified());
            }
            $lastModified = $version->getLastModified();
        }
    }
}
