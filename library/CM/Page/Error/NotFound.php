<?php

class CM_Page_Error_NotFound extends CM_Page_Abstract {

    public function prepareResponse(CM_Response_Page $response) {
        $response->setHeaderNotfound();
    }
}
