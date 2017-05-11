<?php

class CM_Http_Response_View_Ajax extends CM_Http_Response_View_Abstract {

    protected function _processView(array $output) {
        $success = array();
        $query = $this->_request->getQuery();
        if (!isset($query['method'])) {
            throw new CM_Exception_Invalid('No method specified', CM_Exception::WARN);
        }
        if (!preg_match('/^[\w_]+$/i', $query['method'])) {
            throw new CM_Exception_Invalid('Illegal method', CM_Exception::WARN, ['method' => $query['method']]);
        }
        if (!isset($query['params']) || !is_array($query['params'])) {
            throw new CM_Exception_Invalid('Illegal params', CM_Exception::WARN);
        }

        $view = $this->_getView();
        if ($view instanceof CM_View_CheckAccessibleInterface) {
            $view->checkAccessible($this->getRender()->getEnvironment());
        }

        $ajaxMethodName = 'ajax_' . $query['method'];
        $params = CM_Params::factory($query['params'], true);

        $componentHandler = new CM_Frontend_JavascriptContainer_View();

        $this->_setStringRepresentation(get_class($view) . '::' . $ajaxMethodName);

        if (!method_exists($view, $ajaxMethodName)) {
            throw new CM_Exception_Invalid('Method not found', CM_Exception::WARN, ['method' => $ajaxMethodName]);
        }
        $data = $view->$ajaxMethodName($params, $componentHandler, $this);
        $success['data'] = CM_Params::encode($data);

        $frontend = $this->getRender()->getGlobalResponse();
        $frontend->getOnloadReadyJs()->append($componentHandler->compile('this'));
        $jsCode = $frontend->getJs();

        if (strlen($jsCode)) {
            $success['exec'] = $jsCode;
        }
        $output['success'] = $success;
        return $output;
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getUrl()->matchPath('ajax')) {
            $request = clone $request;
            return new self($request, $request->getSite(), $serviceManager);
        }
        return null;
    }

}
