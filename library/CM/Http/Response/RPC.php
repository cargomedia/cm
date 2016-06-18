<?php

class CM_Http_Response_RPC extends CM_Http_Response_Abstract {

    protected function _process() {
        $output = array();
        $this->_runWithCatching(function () use (&$output) {
            $query = CM_Params::factory($this->_request->getQuery());

            $method = $query->getString('method');
            if (!preg_match('/^(?<class>[\w_]+)\.(?<function>[\w_]+)$/', $method, $matches)) {
                throw new CM_Exception_InvalidParam('Illegal method: `' . $method . '`.');
            }
            $class = $matches['class'];
            $function = $matches['function'];
            $params = $query->getArray('params');

            $output['success'] = array('result' => call_user_func_array(array($class, 'rpc_' . $function), $params));
        }, function (CM_Exception $e) use (&$output) {
            $output['error'] = array('type' => get_class($e), 'msg' => $e->getMessagePublic($this->getRender()), 'isPublic' => $e->isPublic());
        });

        $this->setHeader('Content-Type', 'application/json');
        $this->_setContent(json_encode($output));
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Service_Manager $serviceManager) {
        $request = clone $request;
        $site = $request->popPathSiteByMatch();
        if ($request->popPathPart(0) === 'rpc') {
            $request->popPathLanguage();
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
