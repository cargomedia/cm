<?php

class CMService_AwsS3Versioning_Client {

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

    /**
     * @return bool
     */
    public function getVersioningEnabled() {
        $result = $this->_client->getBucketVersioning(array(
            'Bucket' => $this->_bucket,
        ));
        return 'Enabled' == $result->get('Status');
    }

    /**
     * @param string $prefix
     * @return CMService_AwsS3Versioning_Response_Version[]
     */
    public function getVersions($prefix) {
        $options = array(
            'Bucket' => $this->_bucket,
            'Prefix' => (string) $prefix,
        );
        $versionList = array();
        foreach ($this->_client->getListObjectVersionsIterator($options) as $data) {
            $versionList[] = new CMService_AwsS3Versioning_Response_Version($data);
        }
        usort($versionList, function (CMService_AwsS3Versioning_Response_Version $a, CMService_AwsS3Versioning_Response_Version $b) {
            if ($a->getLastModified() == $b->getLastModified()) {
                return 0;
            }
            return $a->getLastModified() < $b->getLastModified() ? 1 : -1;
        });
        return $versionList;
    }
}
