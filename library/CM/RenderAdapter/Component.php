<?php

class CM_RenderAdapter_Component extends CM_RenderAdapter_Abstract {

    /** @var CM_Frontend_ViewResponse */
    protected $_viewResponse;

    /**
     * @return string
     */
    public function fetch() {
        $component = $this->_getComponent();
        $frontend = $this->getRender()->getFrontend();
        $environment = $this->getRender()->getEnvironment();

        $component->checkAccessible($environment);
        $viewResponse = $this->_getViewResponse();
        $this->_prepareViewResponse($viewResponse);

        $frontend->treeExpand($viewResponse);

        $cssClass = implode(' ', $component->getClassHierarchy());
        if (preg_match('#([^/]+)\.tpl$#', $viewResponse->getTemplateName(), $match)) {
            if ($match[1] != 'default') {
                $cssClass .= ' ' . $match[1]; // Include special-tpl name in class (e.g. 'mini')
            }
        }
        $html = '<div id="' . $viewResponse->getAutoId() . '" class="' . $cssClass . '">';
        $html .= $this->getRender()->fetchViewResponse($viewResponse);
        $html .= '</div>';

        $frontend->treeCollapse();
        return $html;
    }

    /**
     * @return string
     */
    protected function _getStackKey() {
        return 'components';
    }

    /**
     * @param CM_Frontend_ViewResponse $viewResponse
     * @return CM_Frontend_ViewResponse
     */
    protected function _prepareViewResponse(CM_Frontend_ViewResponse $viewResponse) {
    }

    /**
     * @return CM_Frontend_ViewResponse
     */
    protected function _getViewResponse() {
        if (null === $this->_viewResponse) {
            $component = $this->_getComponent();
            $environment = $this->getRender()->getEnvironment();

            $viewResponse = new CM_Frontend_ViewResponse($component);
            $viewResponse->setTemplateName('default');
            $component->prepare($environment, $viewResponse);
            $viewResponse->set('viewObj', $component);
            $this->_viewResponse = $viewResponse;
        }
        return $this->_viewResponse;
    }

    /**
     * @return CM_Component_Abstract
     */
    private function _getComponent() {
        return $this->_getView();
    }
}
