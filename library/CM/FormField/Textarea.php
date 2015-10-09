<?php

class CM_FormField_Textarea extends CM_FormField_Text {

    public function validate(CM_Frontend_Environment $environment, $userInput) {
        $converter = new CM_HtmlConverter();
        $userInput = $converter->convertHtmlToPlainText($userInput);
        return parent::validate($environment, $userInput);
    }
}
