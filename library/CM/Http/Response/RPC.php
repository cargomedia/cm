<?php

class CM_Http_Response_RPC extends CM_Http_Response_Abstract {

    protected function _process() {
        $output = [];
        $this->_runWithCatching(function () use (&$output) {
            $query = CM_Params::factory($this->_request->getQuery(), true);

            $method = $query->getString('method');
            if (!preg_match('/^(?<class>[\w_]+)\.(?<function>[\w_]+)$/', $method, $matches)) {
                throw new CM_Exception_InvalidParam('Illegal method.', null, ['method' => $method]);
            }
            $class = $matches['class'];
            $function = $matches['function'];
            $params = $query->getArray('params');

            $output['success'] = ['result' => call_user_func_array([$class, 'rpc_' . $function], $params)];
        }, function (CM_Exception $e) use (&$output) {
            $output['error'] = $e->getClientData($this->getRender());
        });

        $output['deployVersion'] = CM_App::getInstance()->getDeployVersion();
        $this->setHeader('Content-Type', 'application/json');
        $this->_setContent(json_encode($output));
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getUrl()->matchPath('rpc')) {
            $request = clone $request;
            return new self($request, $request->getSite(), $serviceManager);
        }
        return null;
    }

}
