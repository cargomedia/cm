<?php

class CM_I18n {

    protected $string, $params;

    /**
     * @param string $string
     * @param array  $params
     */
    public function __construct($string, array $params) {
        $this->string = $string;
        $this->params = $params;
    }

    /**
     * @param CM_Frontend_Render $render
     * @return string
     */
    public function translate(CM_Frontend_Render $render) {
        return $render->getTranslation($this->string, $this->params);
    }
}
