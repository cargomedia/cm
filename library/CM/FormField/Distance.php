<?php

class CM_FormField_Distance extends CM_FormField_Integer {

    const METERS_PER_MILE = 1609;

    public function parseUserInput($userInput) {
        return parent::parseUserInput($userInput) * self::METERS_PER_MILE;
    }

    /**
     * @return int External Value
     */
    public function getValue() {
        return parent::getValue() / self::METERS_PER_MILE;
    }
}
