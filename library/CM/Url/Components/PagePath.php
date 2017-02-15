<?php

namespace CM\Url\Components;

use CM_Util;
use CM_Exception_Invalid;
use CM_Site_Abstract;
use League\Uri\Components\HierarchicalPath;

class PagePath extends HierarchicalPath {

    /** @var  string */
    protected $_pageClassName;

    public function __construct($pageClassName = '') {
        $this->_pageClassName = $this->_validatePageClassName($pageClassName);
        parent::__construct($this->_getPathFromPageClassName($pageClassName));
    }

    /**
     * @param string $pageClassName
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _validatePageClassName($pageClassName) {
        if (!class_exists($pageClassName) || !is_subclass_of($pageClassName, 'CM_Page_Abstract')) {
            throw new CM_Exception_Invalid('Cannot find valid class definition for page class name', null, [
                'pageClassName' => $pageClassName,
            ]);
        }
        return $pageClassName;
    }

    /**
     * @param string $pageClassName
     * @return string
     */
    protected function _getPathFromPageClassName($pageClassName) {
        $list = explode('_', $pageClassName);
        foreach ($list as $index => $entry) {
            unset($list[$index]);
            if ($entry == 'Page') {
                break;
            }
        }
        foreach ($list as $index => $entry) {
            $list[$index] = CM_Util::uncamelize($entry);
        }
        $path = '/' . implode('/', $list);
        if ($path == '/index') {
            $path = '/';
        }
        return $path;
    }

    /**
     * @param CM_Site_Abstract $site
     * @throws CM_Exception_Invalid
     */
    public function assertSupportedSite(CM_Site_Abstract $site) {
        $namespace = $this->getPageNamespace();
        if (!in_array($namespace, $site->getModules())) {
            throw new CM_Exception_Invalid('Site does not contain namespace', null, [
                'site'      => get_class($site),
                'namespace' => $namespace,
            ]);
        }
    }

    /**
     * @return string
     */
    public function getPageClassName() {
        return $this->_pageClassName;
    }

    /**
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function getPageNamespace() {
        $pageClassName = $this->getPageClassName();
        if (!preg_match('/^([A-Za-z]+)_/', $pageClassName, $matches)) {
            throw new CM_Exception_Invalid('Cannot find namespace of page class name', null, [
                'pageClassName' => $pageClassName,
            ]);
        }
        return $matches[1];
    }
}
