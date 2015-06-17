<?php

class CM_I18n_Phrase {

    /** @var $phrase string */
    protected $phrase;

    /** @var $variables string[] */
    protected $variables;

    /**
     * @param string $phrase
     * @param array  $variables
     */
    public function __construct($phrase, array $variables) {
        $this->phrase = (string) $phrase;
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
