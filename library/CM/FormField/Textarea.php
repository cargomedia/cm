<?php

class CM_FormField_Textarea extends CM_FormField_Text {

    public function prepare(CM_Params $renderParams, CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        parent::prepare($renderParams, $environment, $viewResponse);

        $viewResponse->set('valueHtml', $this->_convertTextToHtml($this->getValue()));
    }

    /**
     * @param string $text
     * @returns string
     */
    protected function _convertTextToHtml($text) {
        $html = CM_Util::htmlspecialchars($text);
        $html = preg_replace('/\r?\n/', '<br>', $html);
        return $html;
    }
}
