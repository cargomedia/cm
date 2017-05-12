<?php

class CM_Http_Response_EmailTracking extends CM_Http_Response_Abstract {

    protected function _process() {
        $params = CM_Params::factory($this->_request->getQuery(), true);
        try {
            $user = $params->getUser('user');
            $mailType = $params->getInt('mailType');

            $action = new CM_Action_Email(CM_Action_Abstract::VIEW, $user, $mailType);
            $action->prepare();
            $action->notify($user);
        } catch (CM_Exception $e) {
            if (in_array(get_class($e), [
                'CM_Exception_Nonexistent',
                'CM_Exception_InvalidParam',
            ])) {
                $e->setSeverity(CM_Exception::WARN);
            }
            throw $e;
        }

        $this->setHeader('Content-Type', 'image/gif');
        $this->_setContent(base64_decode('R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='));
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getUrl()->matchPath('emailtracking')) {
            $request = clone $request;
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
