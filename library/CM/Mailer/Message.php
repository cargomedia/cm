<?php

class CM_Mailer_Message extends Swift_Message {

    /**
     * @return string|null
     */
    public function getHtml() {
        if ('text/html' !== $this->getContentType()) {
            return null;
        }
        return $this->getBody();
    }

    /**
     * @return string|null
     */
    public function getText() {
        $entities = array_merge([$this], $this->getChildren());
        $entity = \Functional\first($entities, function (Swift_Mime_MimeEntity $entity) {
            return 'text/plain' === $entity->getContentType();
        });
        if (!$entity) {
            return null;
        }
        return $entity->getBody();
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

    public static function register() {
        $dep = Swift_DependencyContainer::getInstance();
        if (!$dep->has('message.cm-message')) {
            $dep->register('message.cm-message')->asNewInstanceOf('CM_Mailer_Message');
        }
    }
}
