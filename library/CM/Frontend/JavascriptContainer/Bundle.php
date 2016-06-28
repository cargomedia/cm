<?php

class CM_Frontend_JavascriptContainer_Bundle extends CM_Frontend_JavascriptContainer {

    /** @var array */
    protected $_content = [];

    /** @var string[] */
    protected $_entryPath = [];

    /** @var string[] */
    protected $_sourcePath = [];

    /** @var array */
    protected $_library = [];

    public function compile($generateSourceMaps) {
        $generateSourceMaps = (bool) $generateSourceMaps;
        $cacheKey = __METHOD__ . '_md5:' . md5($this->_getContent()) . '_generateSourceMaps:' . $generateSourceMaps;
        $config = $this->_getConfig([
            'sourceMaps' => $generateSourceMaps,
        ]);

        return CM_Cache_Persistent::getInstance()->get($cacheKey, function () use ($config) {
            return CM_Util::exec(DIR_ROOT . '/bin/jscompile', null, json_encode($config));
        });
    }

    /**
     * @param string      $name     require() module name
     * @param string      $content  inline script
     * @param bool|null   $loadOnly true to execute the inline script, not executed by default
     * @param string|null $path     source map path, use the module name by default
     */
    public function addInlineContent($name, $content, $loadOnly = null, $path = null) {
        $this->_content[] = [
            'name'    => $name,
            'path'    => $path || $name,
            'data'    => $content,
            'require' => (bool) $loadOnly
        ];
    }

    /**
     * @param string $entryPath include all require() recursively + execute the entry code
     */
    public function addEntryPath($entryPath) {
        $this->_entryPath[] = $entryPath;
    }

    /**
     * @param string $sourcePath use those paths to resolve require() calls
     */
    public function addSourcePath($sourcePath) {
        $this->_sourcePath[] = $sourcePath;
    }

    /**
     * Include the js module in the bundle and expose it to the global scope
     *
     * @param string $name       require() module name
     * @param string $sourcePath module source
     */
    public function addLibrary($name, $sourcePath) {
        $this->addSourcePath($sourcePath);
        $this->_library[] = $name;
    }

    /**
     * @param array|null $extra
     * @return array
     */
    protected function _getConfig(array $extra = null) {
        if (null === $extra) {
            $extra = [];
        }
        return array_merge([
            'entries'   => $this->_entryPath,
            'content'   => $this->_content,
            'libraries' => $this->_library,
            'paths'     => $this->_sourcePath
        ], $extra);
    }

    /**
     * @return string
     */
    protected function _getContent() {
        return $this->_getInlineContent() . $this->_getFileContent();
    }

    /**
     * @return string
     */
    protected function _getFileContent() {
        $paths = array_merge($this->_sourcePath, $this->_entryPath);
        return \Functional\reduce_left($paths, function ($path, $index, $collection, $carry) {
            return $carry . \Functional\reduce_left(CM_Util::rglob('*.js', $path), function ($filePath, $index, $collection, $carry) {
                return $carry . md5((new CM_File($filePath))->read());
            }, '');
        }, '');
    }

    /**
     * @return string
     */
    protected function _getInlineContent() {
        return \Functional\reduce_left($this->_content, function ($content, $index, $collection, $carry) {
            return $carry . $content['name'] . $content['data'] . $content['require'];
        }, '');
    }
}
