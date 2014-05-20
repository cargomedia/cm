<?php

class CM_File_Filesystem_Factory {

    public function __construct() {
    }

    /**
     * @param string $className
     * @param array  $options
     * @throws CM_Exception
     * @return CM_File_Filesystem_Adapter
     */
    public function createAdapter($className, array $options) {
        switch ($className) {
            case 'CM_File_Filesystem_Adapter_Local':
                return new CM_File_Filesystem_Adapter_Local($options['pathPrefix']);
                break;
            case 'CM_File_Filesystem_Adapter_AwsS3':
                $acl = isset($options['acl']) ? $options['acl'] : null;
                $pathPrefix = isset($options['pathPrefix']) ? $options['pathPrefix'] : null;
                $clientParams = array(
                    'key'    => $options['key'],
                    'secret' => $options['secret'],
                );
                if (isset($options['region'])) {
                    $clientParams['region'] = $options['region'];
                }
                $client = \Aws\S3\S3Client::factory($clientParams);
                return new CM_File_Filesystem_Adapter_AwsS3($client, $options['bucket'], $acl, $pathPrefix);
                break;
            default:
                throw new CM_Exception('Unsupported adapter class `' . $className . '`.');
        }
    }

    /**
     * @param string $adapterClassName
     * @param array  $options
     * @return CM_File_Filesystem
     */
    public function createFilesystem($adapterClassName, array $options) {
        $adapter = $this->createAdapter($adapterClassName, $options);
        return new CM_File_Filesystem($adapter);
    }
}
