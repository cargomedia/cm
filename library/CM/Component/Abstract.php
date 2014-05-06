<?php

abstract class CM_Component_Abstract extends CM_View_Abstract {

    /**
     * @param CM_Frontend_Environment $environment
     */
    abstract public function checkAccessible(CM_Frontend_Environment $environment);

    /**
     * @param CM_Frontend_Environment $environment
     * @param CM_Frontend_ViewResponse      $viewResponse
     */
    public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
    }

    /**
     * @throws CM_Exception_AuthRequired If no user is set
     */
    protected function _checkViewer(CM_Frontend_Environment $environment) {
        $environment->getViewer(true);
    }

    public function ajax_reload(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Response_View_Ajax $response) {
        return $response->reloadComponent($params->getAll());
    }
}
