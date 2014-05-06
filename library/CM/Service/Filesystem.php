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
     * @param string $path
     * @return CM_File
     */
    public function getFile($path) {
        return new CM_File($path, $this->getFilesystem());
    }

    /**
     * @param bool|null $flush
     */
    public function setup($flush = null) {
        $this->getFilesystem()->getAdapter()->setup();
        if ($flush) {
            $this->getFilesystem()->deleteByPrefix('/');
        }
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
            default:
                throw new CM_Exception('Unsupported adapter class `' . $className . '`.');
        }
    }
}
