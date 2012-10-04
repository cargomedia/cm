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
		return $this->_fetchTpl('meta-description.tpl');
	}

	/**
	 * @return string
	 */
	public function fetchKeywords() {
		return $this->_fetchTpl('meta-keywords.tpl');
	}

	/**
	 * @return string
	 */
	public function fetchTitle() {
		return $this->_fetchTpl('title.tpl');
	}

	protected function _getStackKey() {
		return 'pages';
	}

	/**
	 * @param string $tplName
	 * @return string
	 */
	private function _fetchTpl($tplName) {
		return $this->_renderTemplate($tplName, $this->_getView()->getTplParams());
	}

}
