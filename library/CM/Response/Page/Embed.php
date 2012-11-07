<?php

class CM_Response_Page_Embed extends CM_Response_Page {

	/** @var string|null */
	private $_title;

	/**
	 * @param CM_Request_Abstract $request
	 */
	public function __construct(CM_Request_Abstract $request) {
		parent::__construct($request);
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
		$renderAdapterPage = new CM_RenderAdapter_Page($this->getRender(), $page);
		$renderAdapterLayout = new CM_RenderAdapter_Layout($this->getRender(), $page->getLayout());
		$pageTitle = $renderAdapterPage->fetchTitle();
		$this->_title = $renderAdapterLayout->fetchTitle($pageTitle);
		return $renderAdapterPage->fetch();
	}

	protected function _process() {
		$this->getRender()->getJs()->getTracking()->trackPageview($this->getRequest());
		$html = $this->_processPageLoop($this->getRequest());

		$this->_setContent($html);
	}
}
