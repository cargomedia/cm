<?php

class CM_Frontend_JavascriptContainer_Bundle {

    /** @var string */
    protected $_name;

    /** @var array */
    protected $_content = [];

    /** @var string[] */
    protected $_entryPath = [];

    /** @var string[] */
    protected $_sourcePath = [];

    /** @var string[] */
    protected $_rawPath = [];

    /** @var array */
    protected $_libraryPath = [];

    /** @var array */
    protected $_sourceMapping = [];

    /** @var array */
    protected $_watchPath = [];

    /** @var bool */
    protected $_ignoreMissing = false;

    /**
     * @param string $name
     */
    public function __construct($name) {
        $this->_name = (string) $name;
    }

    /**
     * @param array|null $options
     * @return string
     */
    public function getCode(array $options = null) {
        $name = $this->getName();
        $config = $this->_getConfig($options);
        return CM_Service_Manager::getInstance()->getBundler()->code($name, $config);
    }

    /**
     * @param array|null $options
     * @return string
     */
    public function getSourceMaps(array $options = null) {
        $name = $this->getName();
        $config = $this->_getConfig($options);
        return CM_Service_Manager::getInstance()->getBundler()->sourceMaps($name, $config);
    }

    /**
     * @param bool|null $state
     */
    public function setIgnoreMissing($state = null) {
        $this->_ignoreMissing = (bool) $state;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @param string    $name     require() module name
     * @param string    $content  inline script
     * @param bool|null $loadOnly true to execute the inline script, not executed by default
     * @param bool|null $expose   make the module available with require()
     */
    public function addInlineContent($name, $content, $loadOnly = null, $expose = null) {
        $this->_content[] = [
            'path'    => $name,
            'source'  => $content,
            'execute' => !((bool) $loadOnly),
            'expose'  => (bool) $expose
        ];
    }

    /**
     * @param string $entryPath include all require() recursively + execute the entry code
     */
    public function addEntryPath($entryPath) {
        $this->_entryPath[] = $entryPath;
    }

    /**
     * @param string[] $entryPaths
     */
    public function addEntryPaths($entryPaths) {
        $this->_entryPath = array_merge($this->_entryPath, $entryPaths);
    }

    /**
     * @param string $sourcePath use those paths to resolve require() calls
     */
    public function addSourcePath($sourcePath) {
        $this->_sourcePath[] = $sourcePath;
    }

    /**
     * @param string[] $sourcePaths
     */
    public function addSourcePaths($sourcePaths) {
        $this->_sourcePath = array_merge($this->_sourcePath, $sourcePaths);
    }

    /**
     * @param string $rawPath concatenated source code
     */
    public function addRawPath($rawPath) {
        $this->_rawPath[] = $rawPath;
    }

    /**
     * @param string[] $rawPaths
     */
    public function addRawPaths($rawPaths) {
        $this->_rawPath = array_merge($this->_rawPath, $rawPaths);
    }

    /**
     * @param string $watchPath
     */
    public function addWatchPath($watchPath) {
        $this->_watchPath[] = $watchPath;
    }

    /**
     * @param string[] $watchPaths
     */
    public function addWatchPaths($watchPaths) {
        $this->_watchPath = array_merge($this->_watchPath, $watchPaths);
    }

    /**
     * @param string $libraryPath exposed library path (available in the global scope with require())
     */
    public function addLibraryPath($libraryPath) {
        $this->_libraryPath[] = $libraryPath;
    }

    /**
     * @param string[] $libraryPaths
     */
    public function addLibraryPaths($libraryPaths) {
        $this->_libraryPath = array_merge($this->_libraryPath, $libraryPaths);
    }

    /**
     * @param array $mapping
     */
    public function addSourceMapping($mapping) {
        $this->_sourceMapping = array_merge($this->_sourceMapping, $mapping);
    }

    /**
     * @return array
     */
    public function getSourceMapping() {
        return $this->_sourceMapping;
    }

    /**
     * @param array|null $extra
     * @return array
     */
    protected function _getConfig(array $extra = null) {
        if (null === $extra) {
            $extra = [];
        }
        $makePathRelative = function ($path) {
            return str_replace(DIR_ROOT, '', $path);
        };
        return array_merge([
            'watch'         => \Functional\map($this->_watchPath, $makePathRelative),
            'paths'         => \Functional\map($this->_sourcePath, $makePathRelative),
            'entries'       => \Functional\map($this->_entryPath, $makePathRelative),
            'libraries'     => \Functional\map($this->_libraryPath, $makePathRelative),
            'concat'        => \Functional\map($this->_rawPath, $makePathRelative),
            'content'       => $this->_content,
            'ignoreMissing' => $this->_ignoreMissing,
            'sourceMaps'    => [
                'replace' => $this->getSourceMapping(),
            ],
        ], $extra);
    }
}
