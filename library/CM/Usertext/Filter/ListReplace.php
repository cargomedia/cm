<?php

class CM_Usertext_Filter_ListReplace extends CM_Usertext_Filter_Abstract {

    /** @var Traversable */
    private $_phrases;

    /** @var string */
    private $_replace;

    /**
     * @param Traversable $phrases
     * @param string      $replace
     */
    public function __construct(Traversable $phrases, $replace) {
        $this->_phrases = $phrases;
        $this->_replace = (string) $replace;
    }

    /**
     * @param $text
     * @return string
     */
    public function replaceMatch($text) {
        $text = (string) $text;
        $regex = $this->_toRegex();
        $text = preg_replace($regex, $this->_replace, $text);

        return $text;
    }

    public function transform($text, CM_Frontend_Render $render) {
        return $this->replaceMatch((string) $text);
    }

    /**
     * @param string $userInput
     * @return string|false
     */
    public function getMatch($userInput) {
        if (!$this->isMatch($userInput)) {
            return false;
        }
        $userInput = (string) $userInput;
        foreach ($this->_phrases as $phrase) {
            $regexp = $this->_transformItemToRegex($phrase);
            if (preg_match('#' . $regexp . '#i', $userInput)) {
                return $this->_transformItemToHumanreadable($phrase);
            }
        }

        return false;
    }

    /**
     * @param string $userInput
     * @return boolean
     */
    public function isMatch($userInput) {
        return (boolean) preg_match($this->_toRegex(), (string) $userInput);
    }

    /**
     * @return string
     */
    private function _toRegex() {
        if (0 === iterator_count($this->_phrases)) {
            $phrasesRegex = '#\z.#';
        } else {
            $regexList = [];
            foreach ($this->_phrases as $phrase) {
                $regexList[] = $this->_transformItemToRegex($phrase);
            }
            $phrasesRegex = '#' . implode('|', $regexList) . '#i';
        }
        return $phrasesRegex;
    }

    /**
     * @param string $phrase
     * @return string
     */
    private function _transformItemToHumanreadable($phrase) {
        return str_replace(array('*', '|'), '', $phrase);
    }

    /**
     * @param string $phrase
     * @return string
     */
    protected function _transformItemToRegex($phrase) {
        $regexp = preg_quote($phrase, '#');
        $regexp = str_replace('\*', '[^A-Za-z]*', $regexp);
        $regexp = str_replace('\|', '\b', $regexp);
        $regexp = '\S*' . $regexp . '\S*';
        return $regexp;
    }
}
