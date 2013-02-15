<?php

class CM_AdproviderAdapter_Openx extends CM_AdproviderAdapter_Abstract {

	/**
	 * @return string
	 */
	private function _getHost() {
		return self::_getConfig()->host;
	}

	public function getHtml($zoneData, array $variables) {
		if (!array_key_exists('zoneId', $zoneData)) {
			throw new CM_Exception_Invalid('Missing `zoneId`');
		}
		$zoneId = $zoneData['zoneId'];
		$host = $this->_getHost();
		$html = '<div class="openx-ad" data-zone-id="' . CM_Util::htmlspecialchars($zoneId) . '" data-host="' . CM_Util::htmlspecialchars($host) .
				'"></div>';
		return $html;
	}
}
