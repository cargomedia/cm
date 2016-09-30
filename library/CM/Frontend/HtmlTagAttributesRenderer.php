<?php

class CM_Frontend_HtmlTagAttributesRenderer {

    /** @var CM_Frontend_ViewResponse */
    protected $_viewResponse;

    /**
     * @param CM_Frontend_ViewResponse $viewResponse
     */
    public function __construct(CM_Frontend_ViewResponse $viewResponse) {
        $this->_viewResponse = $viewResponse;
    }

    /**
     * @return string
     */
    public function renderTagAttributes() {
        return 'id="' . $this->_viewResponse->getAutoId() . '" class="' . join(' ', $this->_viewResponse->getCssClasses()) . '"' .
        $this->_getDataHtmlFormatted();
    }

    /**
     * @return string
     */
    protected function _getDataHtmlFormatted() {
        $dataHtml = $this->_viewResponse->getDataHtml();
        $dataAttributes = '';
        foreach ($dataHtml as $key => $value) {
            $dataAttributes .= ' data-' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        return $dataAttributes;
    }
}
