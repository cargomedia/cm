<?php

class CM_Http_Response_Page_ProcessingResult {

    /** @var string|null */
    private $_html = null;

    /** @var string[] */
    private $_pathList = [];

    /** @var CM_Page_Abstract[] */
    private $_pageList = [];

    /**
     * @return bool
     */
    public function hasHtml() {
        return null !== $this->_html;
    }

    /**
     * @return string
     * @throws CM_Exception
     */
    public function getHtml() {
        if (null === $this->_html) {
            throw new CM_Exception('No html available');
        }
        return $this->_html;
    }

    /**
     * @param string $html
     */
    public function setHtml($html) {
        $this->_html = (string) $html;
    }

    /**
     * @return bool
     */
    public function hasPage() {
        return count($this->_pageList) > 0;
    }

    /**
     * @throws CM_Exception
     * @return CM_Page_Abstract
     */
    public function getPage() {
        $page = Functional\last($this->_pageList);
        if (null === $page) {
            throw new CM_Exception('No page available');
        }
        return $page;
    }

    /**
     * @throws CM_Exception
     * @return CM_Page_Abstract
     */
    public function getPageInitial() {
        $page = Functional\first($this->_pageList);
        if (null === $page) {
            throw new CM_Exception('No page available');
        }
        return $page;
    }

    /**
     * @param CM_Page_Abstract $page
     */
    public function addPage(CM_Page_Abstract $page) {
        $this->_pageList[] = $page;
    }

    /**
     * @return bool
     */
    public function hasPath() {
        return count($this->_pathList) > 0;
    }

    /**
     * @return string
     * @throws CM_Exception
     */
    public function getPathInitial() {
        $path = Functional\first($this->_pathList);
        if (null === $path) {
            throw new CM_Exception('No path available');
        }
        return $path;
    }

    /**
     * @return string[]
     */
    public function getPathList() {
        return $this->_pathList;
    }

    /**
     * @throws CM_Exception
     * @return string
     */
    public function getPathTracking() {
        $path = null;
        if ($this->hasPage()) {
            $path = $this->getPageInitial()->getPathVirtualPageView();
        }
        if (null === $path) {
            $path = $this->getPathInitial();
        }
        if (null === $path) {
            throw new CM_Exception('No path available');
        }
        return $path;
    }

    /**
     * @param string $path
     */
    public function addPath($path) {
        $this->_pathList[] = (string) $path;
    }
}
