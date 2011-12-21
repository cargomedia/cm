<?php

class CM_Tracking extends CM_Tracking_Abstract {
	
	private static $_instance = null;
	private $_pageviews = array();
	private $_orders = array();

	/**
	 * @param string $path
	 */
	public function setPageview($path = '') {
		$this->_pageviews = array($path);
	}

	/**
	 * @param string $path
	 */
	public function addPageview($path) {
		$this->_pageviews[] = $path;
	}

	/**
	 * @param string $orderId
	 * @param string $productId
	 * @param float $amount
	 */
	public function addSale($orderId, $productId, $amount) {
		if (!isset($this->_orders[$orderId])) {
			$this->_orders[$orderId] = array();
		}
		$this->_orders[$orderId][$productId] = (float) $amount;
	}

	/**
	 * @return string
	 */
	public function getJs() {
		if (!$this->enabled()) {
			return '';
		}
		
		$js = '';
		foreach ($this->_pageviews as $pageview) {
			if (empty($pageview)) {
				$js .= "_gaq.push(['_trackPageview']);";
			} else {
				$js .= "_gaq.push(['_trackPageview', '" . $pageview . "']);";
			}
		}
		foreach ($this->_orders as $orderId => $products) {
			$amountTotal = 0;
			foreach ($products as $productId => $amount) {
				$amountTotal += $amount;
			}
			$js .= "_gaq.push(['_addTrans', '$orderId', '', '$amountTotal', '', '', '', '', '']);";
			foreach ($products as $productId => $amount) {
				$js .= "_gaq.push(['_addItem', '$orderId', '$productId', 'product-$productId', '', '$amount', '1']);";
			}
			$js .= "_gaq.push(['_trackTrans']);";
		}
		return $js;
	}

	/**
	 * @return string
	 */
	public function getHtml() {
		if (!$this->enabled()) {
			return '';
		}

		$html = '<script type="text/javascript">';
		$html .= 'var _gaq = _gaq || [];';
		$html .= "_gaq.push(['_setAccount', '" . $this->getCode() . "']);";

		if ($domain = parse_url(Config::get()->site_url, PHP_URL_HOST)) {
			$html .= "_gaq.push(['_setDomainName', '" . $domain . "']);";
		}

		$html .= $this->getJs();

		$html .= <<<EOT
(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
EOT;
		$html .= '</script>';
		
		return $html;
	}
	
	/**
	 * @return CM_Tracking
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
