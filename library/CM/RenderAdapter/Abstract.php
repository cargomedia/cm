<?php

abstract class CM_RenderAdapter_Abstract {

    /** @var CM_Frontend_Render */
    private $_render;

    /** @var CM_View_Abstract */
    private $_view;

    /**
     * @param CM_Frontend_Render $render
     * @param CM_View_Abstract   $view
     */
    public function __construct(CM_Frontend_Render $render, CM_View_Abstract $view) {
        $this->_render = $render;
        $this->_view = $view;
    }

    /**
     * @return CM_Frontend_Render
     */
    public function getRender() {
        return $this->_render;
    }

    /**
     * @return CM_View_Abstract
     */
    protected function _getView() {
        return $this->_view;
    }

    /**
     * @param string     $templateName
     * @param array|null $data
     * @return string
     */
    protected function _fetchTemplate($templateName, array $data = null) {
        return $this->getRender()->fetchViewTemplate($this->_getView(), $templateName, $data);
    }
}
