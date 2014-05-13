<?php

class CM_Service_Filesystem extends CM_Class_Abstract {

    /** @var string */
    private $_adapterClass;

    /** @var array */
    private $_adapterOptions;

    /** @var CM_File_Filesystem */
    private $_filesystem;

    /**
     * @param string $adapterClass
     * @param array  $options
     */
    public function __construct($adapterClass, array $options) {
        $this->_adapterClass = (string) $adapterClass;
        $this->_adapterOptions = $options;
    }

    /**
     * @return CM_File_Filesystem
     */
    public function getFilesystem() {
        if (null === $this->_filesystem) {
            $this->_filesystem = new CM_File_Filesystem($this->_createAdapter());
        }
        return $this->_filesystem;
    }

    /**
     * @param bool|null $flush
     */
    public function setup($flush = null) {
        $this->getFilesystem()->getAdapter()->setup();
        if ($flush) {
            $this->flush();
        }
    }

    public function flush() {
        $this->getFilesystem()->deleteByPrefix('/');
    }

    /**
     * @throws CM_Exception
     * @return CM_File_Filesystem_Adapter
     */
    private function _createAdapter() {
        $className = $this->_adapterClass;
        $options = $this->_adapterOptions;
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
}
