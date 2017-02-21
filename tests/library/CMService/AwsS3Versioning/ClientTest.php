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
        $version = (string) $config->$className->version;
        $region = (string) $config->$className->region;
        $key = (string) $config->$className->key;
        $secret = (string) $config->$className->secret;
        if (empty($key) || empty($secret)) {
            $this->markTestSkipped('Missing `key` or `secret` config.');
        }

        $this->_client = new \Aws\S3\S3Client([
            'version'     => $version,
            'region'      => $region,
            'credentials' => [
                'key'    => $key,
                'secret' => $secret,
            ]]);
        $this->_bucket = strtolower(str_replace('_', '-', 'test-' . __CLASS__ . uniqid()));
        $this->_filesystem = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_AwsS3($this->_client, $this->_bucket));

        $this->_client->createBucket(array('Bucket' => $this->_bucket, 'LocationConstraint' => $region));
        $this->_client->putBucketVersioning([
            'Bucket'                  => $this->_bucket,
            'VersioningConfiguration' => [
                'Status' => 'Enabled',
            ]]);

        $this->_restore = new CMService_AwsS3Versioning_Client($this->_client, $this->_bucket, new CM_OutputStream_Null());
    }

    public function tearDown() {
        if ($this->_client) {
            foreach ($this->_restore->getVersions('') as $version) {
                $this->_client->deleteObject([
                    'Bucket'    => $this->_bucket,
                    'Key'       => $version->getKey(),
                    'VersionId' => $version->getId(),
                ]);
            }
            $this->_client->deleteBucket(array('Bucket' => $this->_bucket));
        }
    }

    public function testGetVersioningEnabled() {
        $this->_client->putBucketVersioning([
            'Bucket'                  => $this->_bucket,
            'VersioningConfiguration' => [
                'Status' => 'Enabled',
            ]]);
        $this->assertSame(true, $this->_restore->getVersioningEnabled());
        $this->_client->putBucketVersioning([
            'Bucket'                  => $this->_bucket,
            'VersioningConfiguration' => [
                'Status' => 'Suspended',
            ]]);
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

    public function testRestoreByDeletingNewerVersions() {
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

        $this->_restore->restoreByDeletingNewerVersions('bar', $lastModifiedList[0]->add(new DateInterval('PT1S')));
        $this->assertCount(5, $this->_restore->getVersions('bar'));
        $this->assertSame('mega4', $this->_filesystem->read('bar'));

        $this->_restore->restoreByDeletingNewerVersions('bar', $lastModifiedList[1]);
        $this->assertCount(4, $this->_restore->getVersions('bar'));
        $this->assertSame('mega3', $this->_filesystem->read('bar'));

        $this->_restore->restoreByDeletingNewerVersions('bar', $lastModifiedList[2]);
        $this->assertCount(3, $this->_restore->getVersions('bar'));
        $this->assertSame(false, $this->_filesystem->exists('bar'));

        $this->_restore->restoreByDeletingNewerVersions('bar', $lastModifiedList[4]);
        $this->assertCount(1, $this->_restore->getVersions('bar'));
        $this->assertSame('mega1', $this->_filesystem->read('bar'));

        $this->_restore->restoreByDeletingNewerVersions('bar', $lastModifiedList[4]->sub(new DateInterval('PT1S')));
        $this->assertCount(0, $this->_restore->getVersions('bar'));
        $this->assertSame(false, $this->_filesystem->exists('bar'));
    }

    public function testRestoreByCopyingOldVersion() {
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

        $this->_restore->restoreByCopyingOldVersion('bar', $lastModifiedList[0]->add(new DateInterval('PT1S')));
        $this->assertCount(5, $this->_restore->getVersions('bar'));
        $this->assertSame('mega4', $this->_filesystem->read('bar'));

        $this->_restore->restoreByCopyingOldVersion('bar', $lastModifiedList[1]);
        $this->assertCount(6, $this->_restore->getVersions('bar'));
        $this->assertSame('mega3', $this->_filesystem->read('bar'));

        $this->_restore->restoreByCopyingOldVersion('bar', $lastModifiedList[2]);
        $this->assertCount(7, $this->_restore->getVersions('bar'));
        $this->assertSame(false, $this->_filesystem->exists('bar'));

        $this->_restore->restoreByCopyingOldVersion('bar', $lastModifiedList[4]);
        $this->assertCount(8, $this->_restore->getVersions('bar'));
        $this->assertSame('mega1', $this->_filesystem->read('bar'));

        $this->_restore->restoreByCopyingOldVersion('bar', $lastModifiedList[4]->sub(new DateInterval('PT1S')));
        $this->assertCount(9, $this->_restore->getVersions('bar'));
        $this->assertSame(false, $this->_filesystem->exists('bar'));
    }
}
