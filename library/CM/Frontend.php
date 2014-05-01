<?php

class CM_Frontend {

    /** @var CM_ViewFrontendHandler */
    protected $_onloadHeaderJs = '';

    /** @var CM_ViewFrontendHandler */
    protected $_onloadPrepareJs = '';

    /** @var CM_ViewFrontendHandler */
    protected $_onloadJs = '';

    /** @var CM_ViewFrontendHandler */
    protected $_onloadReadyJs;

    private $_tracking;

    /** @var CM_Render */
    private $_render;

    public function __construct(CM_Render $render) {
        $this->_render = $render;
    }

    public function clear() {
        $this->_onloadHeaderJs->clear();
        $this->_onloadPrepareJs->clear();
        $this->_onloadJs->clear();
        $this->_onloadReadyJs->clear();
    }

    /**
     * @return CM_Tracking
     */
    public function getTracking() {
        if (!$this->_tracking) {
            $this->_tracking = CM_Tracking_Abstract::factory();
        }
        return $this->_tracking;
    }

    /**
     * @return CM_ViewFrontendHandler
     */
    public function getOnloadHeaderJs() {
        return $this->_onloadHeaderJs;
    }

    /**
     * @return CM_ViewFrontendHandler
     */
    public function getOnloadPrepareJs() {
        return $this->_onloadPrepareJs;
    }

    /**
     * @return CM_ViewFrontendHandler
     */
    public function getOnloadJs() {
        return $this->_onloadJs;
    }

    /**
     * @return CM_ViewFrontendHandler
     */
    public function getOnloadReadyJs() {
        return $this->_onloadReadyJs;
    }

    /**
     * @param CM_ViewResponse             $viewResponse
     * @param CM_ViewFrontendHandler|null $frontendHandler
     */
    public function registerViewResponse(CM_ViewResponse $viewResponse, CM_ViewFrontendHandler $frontendHandler = null) {
        $reference = 'cm.views["' . $viewResponse->getAutoId() . '"]';
        $view = $viewResponse->getView();
        $code = $reference . ' = new ' . get_class($view) . '({';
        $code .= 'el:$("#' . $viewResponse->getAutoId() . '").get(0),';
        $code .= 'params:' . CM_Params::encode($view->getParams()->getAllOriginal(), true);

        $parentViewResponse = $this->_render->getStackLast('views');
        if ($parentViewResponse) {
            $code .= ',parent:cm.views["' . $parentViewResponse->getAutoId() . '"]';
        }
        $code .= '});' . PHP_EOL;

        $this->getOnloadPrepareJs()->prepend($code);
        if ($frontendHandler) {
            $this->getOnloadJs()->append($frontendHandler->compile($reference));
        }
    }

    /**
     * @return string
     */
    public function getJs() {
        $jsCode = '';
        $jsCode .= $this->_onloadHeaderJs->compile(null) . PHP_EOL;
        $jsCode .= $this->_onloadPrepareJs->compile(null) . PHP_EOL;
        $jsCode .= $this->_onloadJs->compile(null) . PHP_EOL;
        $jsCode .= $this->_onloadReadyJs->compile(null) . PHP_EOL;
        $jsCode .= $this->getTracking()->getJs();
        return $jsCode;
    }

    /**
     * @return string
     */
    public function getHtml() {
        $html = '<script type="text/javascript">' . PHP_EOL;
        $html .= '$(function() {' . PHP_EOL;
        $html .= $this->_onloadHeaderJs->compile(null) . PHP_EOL;
        $html .= $this->_onloadPrepareJs->compile(null) . PHP_EOL;
        $html .= $this->_onloadJs->compile(null) . PHP_EOL;
        $html .= $this->_onloadReadyJs->compile(null) . PHP_EOL;
        $html .= '});' . PHP_EOL;
        $html .= '</script>' . PHP_EOL;
        $html .= $this->getTracking()->getHtml();
        return $html;
    }
}
