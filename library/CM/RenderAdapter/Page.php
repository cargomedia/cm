<?php

class CM_RenderAdapter_Page extends CM_RenderAdapter_Component {

    public function fetch(array $params = array()) {
        $this->_getView()->setTplParam('pageTitle', $this->fetchTitle());

        return parent::fetch($params);
    }

    /**
     * @return string
     */
    public function fetchDescription() {
        return trim($this->_fetchTpl('meta-description.tpl', true));
    }

    /**
     * @return string
     */
    public function fetchKeywords() {
        return trim($this->_fetchTpl('meta-keywords.tpl', true));
    }

    /**
     * @return string
     */
    public function fetchTitle() {
        return trim($this->_fetchTpl('title.tpl'));
    }

    protected function _getStackKey() {
        return 'pages';
    }

    /**
     * @param string       $tplName
     * @param boolean|null $searchAllNamespaces
     * @return string
     */
    private function _fetchTpl($tplName, $searchAllNamespaces = null) {
        return $this->_renderTemplate($tplName, $this->_getView()->getTplParams(), null, $searchAllNamespaces);
    }
}
