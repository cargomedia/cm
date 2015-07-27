<?php

class CM_I18n_Phrase {

    /** @var $_phrase string */
    protected $_phrase;

    /** @var $_variables string[] */
    protected $_variables;

    /**
     * @param string        $phrase
     * @param string[]|null $variables
     */
    public function __construct($phrase, array $variables = null) {
        $this->_phrase = (string) $phrase;

        if (null !== $variables) {
            $this->_variables = $variables;
        } else {
            $this->_variables = [];
        }
    }

    /**
     * @param CM_Frontend_Render $render
     * @return string
     */
    public function translate(CM_Frontend_Render $render) {
        return $render->getTranslation($this->_phrase, $this->_variables);
    }
}
