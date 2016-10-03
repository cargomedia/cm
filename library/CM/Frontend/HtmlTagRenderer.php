<?php

class CM_Frontend_HtmlTagRenderer {

    /**
     * @param string      $elementName
     * @param string|null $content
     * @param array|null  $attributes
     * @param array|null  $dataHtml
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function renderTag($elementName, $content = null, array $attributes = null, array $dataHtml = null) {
        $elementName = (string) $elementName;
        if ('' === $elementName) {
            throw new CM_Exception_Invalid('Empty element name');
        }
        $content = (string) $content;

        if (null === $attributes) {
            $attributes = [];
        }
        if (null === $dataHtml) {
            $dataHtml = [];
        }
        // http://www.w3.org/TR/html-markup/syntax.html#void-element
        $namesVoid = ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source',
            'track', 'wbr'];

        $html = '<' . $elementName;
        foreach ($attributes as $attributeName => $attributeValue) {
            if (isset($attributeValue)) {
                $html .= ' ' . CM_Util::htmlspecialchars($attributeName) . '="' . CM_Util::htmlspecialchars($attributeValue) . '"';
            }
        }
        foreach ($dataHtml as $dataKey => $dataValue) {
            $html .= ' data-' . CM_Util::htmlspecialchars($dataKey) . '="' . CM_Util::htmlspecialchars($dataValue) . '"';
        }

        if (in_array($elementName, $namesVoid)) {
            $html .= '>';
        } else {
            $html .= '>' . $content . '</' . $elementName . '>';
        }
        return $html;
    }
}
