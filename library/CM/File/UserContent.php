<?php

class CM_File_UserContent extends CM_File implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    const BUCKETS_COUNT = 10000;

    /** @var string */
    private $_pathRelative;

    /** @var string */
    private $_namespace;

    /**
     * @param string                  $namespace
     * @param string                  $filename
     * @param int|null                $sequence
     * @param CM_Service_Manager|null $serviceManager
     */
    public function __construct($namespace, $filename, $sequence = null, CM_Service_Manager $serviceManager = null) {
        $namespace = (string) $namespace;
        $filename = (string) $filename;
        if (null !== $sequence) {
            $sequence = (int) $sequence;
        }
        if (null === $serviceManager) {
            $serviceManager = CM_Service_Manager::getInstance();
        }

        $this->_pathRelative = $this->_calculateRelativeDir($namespace, $filename, $sequence);
        $this->_namespace = $namespace;
        $this->setServiceManager($serviceManager);
        $filesystem = $serviceManager->getUserContent()->getFilesystem($this->getNamespace());

        parent::__construct($this->getPathRelative(), $filesystem);
    }

    /**
     * @return string
     */
    public function getPathRelative() {
        return $this->_pathRelative;
    }

    /**
     * @return string
     */
    public function getNamespace() {
        return $this->_namespace;
    }

    /**
     * @return string
     */
    public function getUrl() {
        $baseUrl = $this->getServiceManager()->getUserContent()->getUrl($this->getNamespace());
        return $baseUrl . '/' . $this->getPathRelative();
    }

    /**
     * @param string   $namespace
     * @param string   $filename
     * @param int|null $sequence
     * @return string
     */
    private function _calculateRelativeDir($namespace, $filename, $sequence = null) {
        $dirs = array();
        $dirs[] = $namespace;
        if (null !== $sequence) {
            $dirs[] = $sequence % self::BUCKETS_COUNT;
        }
        $dirs[] = $filename;
        return implode('/', $dirs);
    }
}
