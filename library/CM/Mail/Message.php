<?php

class CM_Mail_Message extends Swift_Message implements CM_ArrayConvertible {

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

    public function toArray() {
        return [
            'subject'       => $this->getSubject(),
            'html'          => $this->getHtml(),
            'text'          => $this->getText(),
            'sender'        => $this->getSender(),
            'replyTo'       => $this->getReplyTo(),
            'to'            => $this->getTo(),
            'cc'            => $this->getCc(),
            'bcc'           => $this->getBcc(),
            'customHeaders' => $this->getCustomHeaders(),
        ];
    }

    public static function fromArray(array $array) {
        $message = new self($array['subject']);
        if (null !== $array['sender']) {
            foreach ($array['sender'] as $address => $name) {
                $message->setSender($address, $name);
            }
        }
        if (null !== $array['to']) {
            foreach ($array['to'] as $address => $name) {
                $message->addTo($address, $name);
            }
        }
        if (null !== $array['replyTo']) {
            foreach ($array['replyTo'] as $address => $name) {
                $message->addReplyTo($address, $name);
            }
        }
        if (null !== $array['cc']) {
            foreach ($array['cc'] as $address => $name) {
                $message->addCc($address, $name);
            }
        }
        if (null !== $array['bcc']) {
            foreach ($array['bcc'] as $address => $name) {
                $message->addBcc($address, $name);
            }
        }
        if (null !== $array['text']) {
            $message->setBodyWithAlternative($array['text'], $array['html']);
        }
        foreach ($array['customHeaders'] as $label => $valueList) {
            foreach ($valueList as $value) {
                $message->getHeaders()->addTextHeader($label, $value);
            }
        }
        return $message;
    }

    public static function register() {
        $dep = Swift_DependencyContainer::getInstance();
        if (!$dep->has('message.cm-message')) {
            $dep->register('message.cm-message')->asNewInstanceOf('CM_Mail_Message');
        }
    }
}
