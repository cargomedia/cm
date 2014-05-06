<?php

class CM_RenderAdapter_Component extends CM_RenderAdapter_Abstract {

    /**
     * @return string
     */
    public function fetch() {
        $frontend = $this->getRender()->getFrontend();
        $renderEnvironment = $this->getRender()->getEnvironment();
        $this->_getComponent()->checkAccessible($renderEnvironment);

        $viewResponse = $this->_getPreparedViewResponse($renderEnvironment);
        $viewResponse->set('viewObj', $this->_getComponent());

        $frontend->treeExpand($viewResponse);

        $cssClass = implode(' ', $this->_getComponent()->getClassHierarchy());
        if (preg_match('#([^/]+)\.tpl$#', $viewResponse->getTemplateName(), $match)) {
            if ($match[1] != 'default') {
                $cssClass .= ' ' . $match[1]; // Include special-tpl name in class (e.g. 'mini')
            }
        }
        $html = '<div id="' . $viewResponse->getAutoId() . '" class="' . $cssClass . '">';
        $html .= $this->getRender()->fetchViewResponse($viewResponse);
        $html .= '</div>';

        $frontend->registerViewResponse($viewResponse);

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
     * @param CM_Frontend_Environment $environment
     * @return CM_Frontend_ViewResponse
     */
    protected function _getPreparedViewResponse(CM_Frontend_Environment $environment) {
        $component = $this->_getComponent();
        $viewResponse = new CM_Frontend_ViewResponse($component);
        $viewResponse->setTemplateName('default');
        $component->prepare($environment, $viewResponse);
        return $viewResponse;
    }

    /**
     * @return CM_Component_Abstract
     */
    private function _getComponent() {
        return $this->_getView();
    }
}
