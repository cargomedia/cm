<?php

abstract class CM_Component_Abstract extends CM_View_Abstract {

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
     * @param CM_RenderEnvironment        $environment
     * @param CM_ViewResponse             $viewResponse
     * @param CM_ViewFrontendHandler $frontendHandler
     * @internal param \CM_Params $params
     */
    public function prepare(CM_RenderEnvironment $environment, CM_ViewResponse $viewResponse, CM_ViewFrontendHandler $frontendHandler) {
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

    public function ajax_reload(CM_Params $params, CM_ViewFrontendHandler $handler, CM_Response_View_Ajax $response) {
        return $response->reloadComponent($params->getAll());
    }
}
