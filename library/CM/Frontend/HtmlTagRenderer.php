<?php

class CM_Frontend_HtmlTagRenderer {

    /**
     * @param string      $elementName
     * @param string|null $content
     * @param array|null  $attributes
     * @param array|null  $dataAttributes
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function renderTag($elementName, $content = null, array $attributes = null, array $dataAttributes = null) {
        $elementName = (string) $elementName;
        if ('' === $elementName) {
            throw new CM_Exception_Invalid('Empty element name');
        }
        $content = (string) $content;

        if (null === $attributes) {
            $attributes = [];
        }
        if (null === $dataAttributes) {
            $dataAttributes = [];
        }
        // http://www.w3.org/TR/html-markup/syntax.html#void-element
        $namesVoid = ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source',
            'track', 'wbr'];

        $html = '<' . $elementName;

        foreach ($dataAttributes as $dataKey => $dataValue) {
            $attributes['data-' . $dataKey] = $dataValue;
        }

        foreach ($attributes as $attributeName => $attributeValue) {
            if (isset($attributeValue)) {
                $html .= ' ' . CM_Util::htmlspecialchars($attributeName) . '="' . CM_Util::htmlspecialchars($attributeValue) . '"';
            }
        }

        if (in_array($elementName, $namesVoid)) {
            $html .= '>';
        } else {
            $html .= '>' . $content . '</' . $elementName . '>';
        }
        return $html;
    }
}
