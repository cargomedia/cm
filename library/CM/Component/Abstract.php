<?php

abstract class CM_Component_Abstract extends CM_View_Abstract {

    /**
     * @param CM_RenderEnvironment $environment
     */
    abstract public function checkAccessible(CM_RenderEnvironment $environment);

    /**
     * @param CM_RenderEnvironment $environment
     * @param CM_ViewResponse      $viewResponse
     */
    public function prepare(CM_RenderEnvironment $environment, CM_ViewResponse $viewResponse) {
    }

    /**
     * @throws CM_Exception_AuthRequired If no user is set
     */
    protected function _checkViewer(CM_RenderEnvironment $environment) {
        $environment->getViewer(true);
    }

    public function ajax_reload(CM_Params $params, CM_ViewFrontendHandler $handler, CM_Response_View_Ajax $response) {
        return $response->reloadComponent($params->getAll());
    }
}
