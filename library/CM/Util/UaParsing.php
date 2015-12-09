<?php

class CM_Util_UaParsing extends Jenssegers\Agent\Agent {

    protected $_CMRules = [
        'Samsung' => 'SM-G925F',
    ];

    public function getRules() {
        return $this->mergeRules(parent::getRules(), $this->_CMRules);
    }

    /**
     * @param string $brand
     * @param string $modelToken
     * @return bool
     */
    public function isAModel($brand, $modelToken) {
        $brand = (string) $brand;
        $modelToken = (string) $modelToken;

        $this->setDetectionType(self::DETECTION_TYPE_EXTENDED);
        $_rules = $this->getRules();

        return $this->match($_rules[$brand]) && (bool) preg_match(sprintf('#%s#is', $_rules[$brand]), $modelToken);
    }

}
