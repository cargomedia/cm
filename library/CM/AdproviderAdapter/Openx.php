<?php

class CM_AdproviderAdapter_Openx extends CM_AdproviderAdapter_Abstract {

	/**
	 * @return string
	 */
	private function _getHost() {
		return self::_getConfig()->host;
	}

	public function getHtml($zoneData) {
		if (!array_key_exists('zoneId', $zoneData)) {
			throw new CM_Exception_Invalid('Missing `zoneId`');
		}
		$zoneId = $zoneData['zoneId'];
		$host = $this->_getHost();
		$html = <<<EOF
<div class="adSpace" data-zone-id=$zoneId data-host=$host></div>

EOF;
		return $html;
	}
}
