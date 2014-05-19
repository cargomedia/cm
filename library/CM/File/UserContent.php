<?php

class CM_File_UserContent extends CM_File {

    const BUCKETS_COUNT = 10000;

    /** @var string */
    private $_pathRelative;

    /**
     * @param string   $namespace
     * @param string   $filename
     * @param int|null $sequence
     */
    public function __construct($namespace, $filename, $sequence = null) {
        $namespace = (string) $namespace;
        $filename = (string) $filename;
        if (null !== $sequence) {
            $sequence = (int) $sequence;
        }

        $this->_pathRelative = $this->_calculateRelativeDir($namespace, $filename, $sequence);
        $filesystem = CM_Service_Manager::getInstance()->getFilesystems()->getUserfiles();
        parent::__construct($this->getPathRelative(), $filesystem);
    }

    /**
     * @return string
     */
    public function getPathRelative() {
        return $this->_pathRelative;
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
