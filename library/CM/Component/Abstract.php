<?php

abstract class CM_Component_Abstract extends CM_View_Abstract {

    /** @var string */
    protected $_tplName = 'default.tpl';

    /** @var CM_ComponentFrontendHandler */
    protected $_js = null;

    /**
     * @param CM_Params|array|null $params
     */
    public function __construct($params = null) {
        if (is_null($params)) {
            $params = CM_Params::factory();
        }
        if (is_array($params)) {
            $params = CM_Params::factory($params);
        }
        $this->_params = $params;
        $this->_js = new CM_ComponentFrontendHandler();
    }

    /**
     * Checks if a component can be accessed by the currently set user
     *
     * Access for everyone is default. Should be overloaded by every component
     *
     * @param CM_RenderEnvironment $environment
     * @return mixed
     */
    abstract public function checkAccessible(CM_RenderEnvironment $environment);

    /**
     * @return CM_ComponentFrontendHandler
     */
    public function getFrontendHandler() {
        return $this->_js;
    }

    /**
     * @param CM_RenderEnvironment        $environment
     * @param CM_ViewResponse             $viewResponse
     * @param CM_ComponentFrontendHandler $frontendHandler
     * @internal param \CM_Params $params
     */
    public function prepare(CM_RenderEnvironment $environment, CM_ViewResponse $viewResponse, CM_ComponentFrontendHandler $frontendHandler) {
    }

    /**
     * Get auto id prefixed id value for an html element.
     *
     * @param string $id_value
     * @return string
     */
    final public function getTagAutoId($id_value) {
        return $this->getAutoId() . '-' . $id_value;
    }

    /**
     * Checks if a user is set on the component
     *
     * @throws CM_Exception_AuthRequired If no user is set
     */
    protected function _checkViewer(CM_RenderEnvironment $environment) {
        $environment->getViewer(true);
    }

    public function ajax_reload(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_View_Ajax $response) {
        return $response->reloadComponent($params->getAll());
    }

    /**
     * @param string $property
     * @param mixed  $value
     */
    protected function _setJsParam($property, $value) {
        $this->_js->setParam($property, $value);
    }
}
