<?php

abstract class CM_View_Abstract extends CM_Class_Abstract {

    const CONTEXT_ALL = 1;
    const CONTEXT_JAVASCRIPT = 2;
    const CONTEXT_CSS = 3;

    /** @var CM_Params */
    protected $_params;

    /**
     * @param CM_Params|array|null $params
     */
    public function __construct($params = null) {
        if (!$params instanceof CM_Params) {
            $params = CM_Params::factory($params);
        }
        $this->_params = $params;
    }

    /**
     * @return CM_Params
     */
    public function getParams() {
        return $this->_params;
    }

    public function ajax_loadComponent(CM_Params $params, CM_ViewFrontendHandler $handler, CM_Response_View_Ajax $response) {
        return $response->loadComponent($params);
    }

    public function ajax_loadPage(CM_Params $params, CM_ViewFrontendHandler $handler, CM_Response_View_Ajax $response) {
        return $response->loadPage($params, $response);
    }

    /**
     * @param CM_Model_User $user
     * @param string        $event
     * @param mixed|null    $data
     */
    public static function stream(CM_Model_User $user, $event, $data = null) {
        $namespace = get_called_class() . ':' . $event;
        CM_Model_StreamChannel_Message_User::publish($user, $namespace, $data);
    }

    /**
     * @param string               $className
     * @param CM_Params|array|null $params
     * @throws CM_Exception_Invalid
     * @return static
     */
    public static function factory($className, $params = null) {
        if (!class_exists($className) || !is_a($className, get_called_class(), true)) {
            throw new CM_Exception_Invalid('Cannot find valid class definition for view `' . $className . '`.');
        }
        $view = new $className($params);
        return $view;
    }
}
