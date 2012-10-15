<?php

class CM_Response_Page_Embed extends CM_Response_Page {

	/** @var string */
	private $_parentId;

	/** @var string|null */
	private $_title;

	/**
	 * @param CM_Request_Abstract $request
	 * @param string              $parentId
	 */
	public function __construct(CM_Request_Abstract $request, $parentId) {
		parent::__construct($request);
		$this->_parentId = (string) $parentId;
	}

	public function process() {
		$html = $this->_processPageLoop($this->getRequest());

		$this->_setContent($html);
	}

	/**
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	public function getTitle() {
		if (null === $this->_title) {
			throw new CM_Exception_Invalid('Unprocessed page has no title');
		}
		return $this->_title;
	}

	/**
	 * @param CM_Page_Abstract $page
	 * @return string
	 */
	protected function _renderPage(CM_Page_Abstract $page) {
		$renderAdapter = new CM_RenderAdapter_Page($this->getRender(), $page);
		$this->_title = $renderAdapter->fetchTitle();
		return $renderAdapter->fetch(array('parentId' => $this->_parentId));
	}

}
