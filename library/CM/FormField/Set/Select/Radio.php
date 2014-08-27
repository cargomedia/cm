<?php

class CM_FormField_Set_Select_Radio extends CM_FormField_Set_Select {

    public function prepare(CM_Params $renderParams, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('itemValue', $renderParams->get('item'));
    }
}
