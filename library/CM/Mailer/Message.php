<?php

class CM_Mailer_Message extends Swift_Message {

    /** @var string|null */
    private $_text;

    /** @var string|null */
    private $_html;

    /**
     * @return string|null
     */
    public function getHtml() {
        return $this->_html;
    }

    /**
     * @return string|null
     */
    public function getText() {
        return $this->_text;
    }

    /**
     * @return array
     */
    public function getCustomHeaders() {
        $result = [];
        $headers = $this->getHeaders()->getAll();
        foreach ($headers as $header) {
            if (
                $header instanceof Swift_Mime_Headers_UnstructuredHeader &&
                preg_match('/^X-.*/', $header->getFieldName())
            ) {
                $result[$header->getFieldName()][] = $header->getFieldBody();
            }
        }
        return $result;
    }

    /**
     * @param string      $text
     * @param string|null $html
     * @return $this
     */
    public function setBodyWithAlternative($text, $html = null) {
        $text = (string) $text;
        $html = null !== $html ? (string) $html : null;
        if (null === $html) {
            $this->setBody($text, 'text/plain');
        } else {
            $this->setBody($html, 'text/html');
            $this->addPart($text, 'text/plain');
        }
        return $this;
    }

    public function addPart($body, $contentType = null, $charset = null) {
        if ($contentType === 'text/html') {
            $this->_html = (string) $body;
        } elseif ($contentType === 'text/plain') {
            $this->_text = (string) $body;
        }
        return parent::addPart($body, $contentType, $charset);
    }

    public function setBody($body, $contentType = null, $charset = null) {
        if ($contentType === 'text/html') {
            $this->_html = (string) $body;
        } elseif ($contentType === 'text/plain') {
            $this->_text = (string) $body;
        }
        return parent::setBody($body, $contentType, $charset);
    }

    public static function register() {
        $dep = Swift_DependencyContainer::getInstance();
        if (!$dep->has('message.cm-message')) {
            $dep->register('message.cm-message')->asNewInstanceOf('CM_Mailer_Message');
        }
    }
}
