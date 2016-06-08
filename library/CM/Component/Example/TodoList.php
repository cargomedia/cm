<?php

class CM_Component_Example_TodoList extends CM_Component_Abstract {

    public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->getJs()->setProperty('_todoList', new CM_Paging_Example_Todo());
    }

    public function checkAccessible(CM_Frontend_Environment $environment) {
        if (!$environment->isDebug()) {
            throw new CM_Exception_NotAllowed();
        }
    }

    public function ajax_delete(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Http_Response_View_Ajax $response) {
        $todo = $params->get('todo');
        $todo->delete();
    }

    public function ajax_changeState(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Http_Response_View_Ajax $response) {
        $todo = $params->get('todo');
        $state = $params->getInt('state');
        $todo->setState($state);
        $todo->commit();
    }
}
