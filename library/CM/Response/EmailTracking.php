<?php

class CM_Response_EmailTracking extends CM_Response_Abstract {

    protected function _process() {
        $params = CM_Params::factory($this->_request->getQuery(), true);
        try {
            $user = $params->getUser('user');
            $mailType = $params->getInt('mailType');

            $action = new CM_Action_Email(CM_Action_Abstract::VIEW, $user, $mailType);
            $action->prepare();
            $action->notify($user, $mailType);
        } catch (CM_Exception_Nonexistent $e) {
            // will be ignored
        }

        $this->setHeader('Content-Type', 'image/gif');
        $this->_setContent(base64_decode('R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='));
    }

    public static function match(CM_Http_Request_Abstract $request) {
        return $request->getPathPart(0) === 'emailtracking';
    }
}
