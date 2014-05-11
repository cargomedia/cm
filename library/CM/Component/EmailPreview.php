<?php

class CM_Component_EmailPreview extends CM_Component_Abstract {

    public function checkAccessible(CM_Render $render) {
    }

    public function prepare() {
        $email = $this->_params->get('email');
        if (!$email instanceof CM_Mail) {
            throw new CM_Exception_InvalidParam('Invalid `email` param');
        }

        list($subject, $html, $plainText) = $email->render();
        $this->setTplParam('plainText', $plainText);
        $this->_setJsParam('html', $html);
    }
}
