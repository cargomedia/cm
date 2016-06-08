<?php

class CM_Component_Example_TodoList extends CM_Component_Abstract {

    public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('todoList', new CM_Paging_Example_Todo());

    }

    public function checkAccessible(CM_Frontend_Environment $environment) {
        if (!$environment->isDebug()) {
            throw new CM_Exception_NotAllowed();
        }
    }
}
