<?php

class CM_FormField_Distance extends CM_FormField_Integer {

    public function parseUserInput($userInput) {
        return parent::parseUserInput($userInput) * 1609;
    }


    public function validate(CM_Frontend_Environment $environment, $userInput) {
        parent::validate($environment, $userInput);
    }

    /**
     * @return int External Value
     */
    public function getValue() {
        return parent::getValue() / 1609;
    }
}
