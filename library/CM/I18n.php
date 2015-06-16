<?php

class CM_I18n {

    protected $phrase, $variables;

    /**
     * @param string $phrase
     * @param array  $variables
     */
    public function __construct($phrase, array $variables) {
        $this->phrase = $phrase;
        $this->variables = $variables;
    }

    /**
     * @param CM_Frontend_Render $render
     * @return string
     */
    public function translate(CM_Frontend_Render $render) {
        return $render->getTranslation($this->phrase, $this->variables);
    }
}
