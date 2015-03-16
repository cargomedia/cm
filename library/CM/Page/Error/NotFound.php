<?php

class CM_Page_Error_NotFound extends CM_Page_Abstract {

    public function prepareResponse(CM_Frontend_Environment $environment, CM_Http_Response_Page $response) {
        $response->setHeaderNotfound();
    }
}
