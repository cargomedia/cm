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

    /**
     * Concatenate a javascript code $line with $var by reference.
     *
     * @param string  $line
     * @param string  &$var    reference
     * @param boolean $prepend OPTIONAL
     */
    public static function concat_js($line, &$var, $prepend = false) {
        $line = trim($line);

        if ($line) {
            if (substr($line, -1) != ';') {
                $line .= ';';
            }
            $line .= "\n";
            if ($prepend) {
                $var = $line . $var;
            } else {
                $var = $var . $line;
            }
        }
    }

    public function clear() {
        $this->_onloadHeaderJs = '';
        $this->_onloadPrepareJs = '';
        $this->_onloadJs = '';
        $this->_onloadReadyJs = '';
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
        return $this->compile() . PHP_EOL . $this->getTracking()->getJs();
    }

    /**
     * @return string
     */
    public function compile() {
        $operations = array(
            $this->_onloadHeaderJs->compile(null),
            $this->_onloadPrepareJs->compile(null),
            $this->_onloadJs->compile(null),
            $this->_onloadReadyJs->compile(null),
        );
        return implode(PHP_EOL, $operations);
    }

    /**
     * @return string
     */
    public function renderScripts() {
        return '<script type="text/javascript">' . PHP_EOL . '$(function() {' . $this->compile() . '});' . PHP_EOL . '</script>' . PHP_EOL;
    }
}
