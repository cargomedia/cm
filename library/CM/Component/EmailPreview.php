<?php

class CM_Component_EmailPreview extends CM_Component_Abstract {

    public function checkAccessible(CM_Frontend_Environment $environment) {
    }

    public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $email = $this->_params->get('email');
        if (!$email instanceof CM_Mail) {
            throw new CM_Exception_InvalidParam('Invalid `email` param');
        }

        list($subject, $html, $plainText) = $email->render();
        $viewResponse->set('plainText', $plainText);
        $viewResponse->getJs()->setProperty('html', $html);
    }
}
