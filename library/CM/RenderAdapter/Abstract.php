<?php

abstract class CM_RenderAdapter_Abstract {

    /**
     * @var CM_Render
     */
    private $_render;

    /**
     * @var CM_View_Abstract
     */
    private $_view;

    /**
     * @param CM_Render $render
     * @param           $view
     */
    public function __construct(CM_Render $render, CM_View_Abstract $view) {
        $this->_render = $render;
        $this->_view = $view;
    }

    /**
     * @return CM_Render
     */
    public function getRender() {
        return $this->_render;
    }

    /**
     * @param string|null $tplName
     * @param array|null  $variables
     * @param bool|null   $isolated
     * @return string
     */
    protected function _renderTemplate($tplName = null, array $variables = null, $isolated = null) {
        $tplPath = $this->_getTplPath($tplName);
        return $this->getRender()->renderTemplate($tplPath, $variables, $isolated);
    }

    /**
     * Return tpl path
     *
     * First try theme for current component
     * try all themes
     * Then try parents -> for all themes again
     *
     * @param string $tplName
     * @return string
     * @throws CM_Exception
     */
    protected function _getTplPath($tplName) {
        return $this->getRender()->getTemplatePath($this->_getView(), $tplName);
    }

    /**
     * @return CM_View_Abstract
     */
    protected function _getView() {
        return $this->_view;
    }
}
