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
        $this->_client->getConfig()->set('curl.options', array('body_as_string' => true)); // https://github.com/aws/aws-sdk-php/issues/140#issuecomment-25117635
        $this->_bucket = strtolower(str_replace('_', '-', 'test-' . __CLASS__ . uniqid()));
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

    public function testRestoreByDate() {
        $this->_filesystem->write('bar', 'mega1');
        sleep(1);
        $this->_filesystem->write('bar', 'mega2');
        sleep(1);
        $this->_filesystem->delete('bar');
        sleep(1);
        $this->_filesystem->write('bar', 'mega3');
        sleep(1);
        $this->_filesystem->write('bar', 'mega4');

        $versionList = $this->_restore->getVersions('bar');
        /** @var DateTime[] $lastModifiedList */
        $lastModifiedList = Functional\map($versionList, function (CMService_AwsS3Versioning_Response_Version $version) {
            return $version->getLastModified();
        });

        $this->_restore->restoreByDate('bar', $lastModifiedList[1]);
        $this->assertCount(4, $this->_restore->getVersions('bar'));
        $this->assertSame('mega3', $this->_filesystem->read('bar'));

        $this->_restore->restoreByDate('bar', $lastModifiedList[2]);
        $this->assertCount(3, $this->_restore->getVersions('bar'));
        $this->assertSame(false, $this->_filesystem->exists('bar'));

        $this->_restore->restoreByDate('bar', $lastModifiedList[4]);
        $this->assertCount(1, $this->_restore->getVersions('bar'));
        $this->assertSame('mega1', $this->_filesystem->read('bar'));

        $this->_restore->restoreByDate('bar', $lastModifiedList[4]->sub(new DateInterval('PT1S')));
        $this->assertCount(0, $this->_restore->getVersions('bar'));
        $this->assertSame(false, $this->_filesystem->exists('bar'));
    }
}
