<?php

class CMService_S3VersionRestore {

    /** @var Aws\S3\S3Client */
    private $_client;

    /** @var string */
    private $_bucket;

    /**
     * @param Aws\S3\S3Client $client
     * @param string          $bucket
     */
    public function __construct(Aws\S3\S3Client $client, $bucket) {
        $this->_client = $client;
        $this->_bucket = (string) $bucket;
    }

    public function getVersioningEnabled() {
        $result = $this->_client->getBucketVersioning(array(
            'Bucket' => $this->_bucket,
        ));
        return 'Enabled' == $result->get('Status');
    }

    /**
     * @param string $key
     * @return string[]
     */
    public function getVersions($key) {
        $key = (string) $key;
        $result = $this->_client->listObjectVersions(array(
            'Bucket' => $this->_bucket,
            'Prefix' => $key,
        ));
        print_r($result);
    }
}
