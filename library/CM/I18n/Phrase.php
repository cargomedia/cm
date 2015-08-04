<?php

class CM_I18n_Phrase {

    /** @var $_phrase string */
    protected $_phrase;

    /** @var $_variables string[] */
    protected $_variables;

    /**
     * @param string        $phrase
     * @param string[]|null $variables
     * @throws CM_Exception_Invalid
     */
    public function __construct($phrase, array $variables = null) {
        $phrase = (string) $phrase;
        if ('' === $phrase) {
            throw new CM_Exception_Invalid('I18n phrase should not be empty');
        }
        $this->_phrase = $phrase;
        if (null === $variables) {
            $variables = [];
        }
        $this->_variables = $variables;
    }

    /**
     * @param CM_Frontend_Render $render
     * @return string
     */
    public function translate(CM_Frontend_Render $render) {
        return $render->getTranslation($this->_phrase, $this->_variables);
    }
}
