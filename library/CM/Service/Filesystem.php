<?php

class CM_Service_Filesystem extends CM_Class_Abstract {

    /** @var CM_File_Filesystem */
    private $_filesystem;

    /**
     * @param string $adapterClass
     * @param array  $options
     */
    public function __construct($adapterClass, array $options) {
        $adapter = $this->_adapterFactory($adapterClass, $options);
        $this->_filesystem = new CM_File_Filesystem($adapter);
    }

    /**
     * @return CM_File_Filesystem
     */
    public function getFilesystem() {
        return $this->_filesystem;
    }

    /**
     * @param bool|null $flush
     */
    public function setup($flush = null) {
        $this->_filesystem->getAdapter()->setup();
        if ($flush) {
            $this->_filesystem->deleteByPrefix('/');
        }
    }

    /**
     * @param string $className
     * @param array  $options
     * @throws CM_Exception
     * @return CM_File_Filesystem_Adapter
     */
    private function _adapterFactory($className, array $options) {
        switch ($className) {
            case 'CM_File_Filesystem_Adapter_Local':
                return new CM_File_Filesystem_Adapter_Local($options['pathPrefix']);
                break;
            default:
                throw new CM_Exception('Unsupported adapter class `' . $className . '`.');
        }
    }
}
