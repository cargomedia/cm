<?php

class CMService_AwsS3Versioning_Response_Version {

    /**
     * @param array $data
     */
    public function __construct(array $data) {
        $key = $data['Key'];
        $id = $data['VersionId'];
        $lastModified = $data['LastModified'];
    }
}
