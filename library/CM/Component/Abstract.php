<?php

abstract class CM_Component_Abstract extends CM_View_Abstract {

    /**
     * @param CM_Frontend_Environment $environment
     * @throws CM_Exception_NotAllowed
     * @throws CM_Exception_AuthRequired
     */
    abstract public function checkAccessible(CM_Frontend_Environment $environment);

    /**
     * @param CM_Frontend_Environment  $environment
     * @param CM_Frontend_ViewResponse $viewResponse
     */
    abstract public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse);

    /**
     * @param CM_Frontend_Environment $environment
     * @throws CM_Exception_AuthRequired
     */
    protected function _checkViewer(CM_Frontend_Environment $environment) {
        $environment->getViewer(true);
    }

    public function ajax_reload(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Response_View_Ajax $response) {
        return $response->reloadComponent($params->getAll());
    }
}
