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
                $clientParams = [
                    'version'     => $options['version'],
                    'region'      => $options['region'],
                    'credentials' => [
                        'key'    => $options['key'],
                        'secret' => $options['secret'],
                    ]
                ];
                $client = new \Aws\S3\S3Client($clientParams);
                $client->registerStreamWrapper();
                return new CM_File_Filesystem_Adapter_AwsS3($client, $options['bucket'], $acl, $pathPrefix);
                break;
            default:
                throw new CM_Exception('Unsupported adapter class.', null, ['className' => $className]);
        }
    }

    /**
     * @param string      $adapterClassName
     * @param array       $options
     * @param string|null $secondaryClassName
     * @param array|null  $secondaryOptions
     * @return CM_File_Filesystem
     */
    public function createFilesystem($adapterClassName, array $options, $secondaryClassName = null, array $secondaryOptions = null) {
        $adapter = $this->createAdapter($adapterClassName, $options);
        $filesystem = new CM_File_Filesystem($adapter);

        if (null !== $secondaryClassName && null !== $secondaryOptions) {
            $secondaryAdapter = $this->createAdapter($secondaryClassName, $secondaryOptions);
            $secondaryFilesystem = new CM_File_Filesystem($secondaryAdapter);
            $filesystem->addSecondary($secondaryFilesystem);
        }

        return $filesystem;
    }
}
