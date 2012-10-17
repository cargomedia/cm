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
		$rand = rand(1, 999999);
		$uniqid = 'a' . rand(1, 999999);
		$html = <<<EOF
<script type='text/javascript'><!--//<![CDATA[
   var m3_u = (location.protocol=='https:'?'https://$host/delivery/ajs.php':'http://$host/delivery/ajs.php');
   var m3_r = Math.floor(Math.random()*99999999999);
   if (!document.MAX_used) document.MAX_used = ',';
   document.write ("<scr"+"ipt type='text/javascript' src='"+m3_u);
   document.write ("?zoneid=$zoneId");
   document.write ('&amp;cb=' + m3_r);
   if (document.MAX_used != ',') document.write ("&amp;exclude=" + document.MAX_used);
   document.write (document.charset ? '&amp;charset='+document.charset : (document.characterSet ? '&amp;charset='+document.characterSet : ''));
   document.write ("&amp;loc=" + escape(window.location));
   if (document.referrer) document.write ("&amp;referer=" + escape(document.referrer));
   if (document.context) document.write ("&context=" + escape(document.context));
   if (document.mmm_fo) document.write ("&amp;mmm_fo=1");
   document.write ("'><\/scr"+"ipt>");
//]]>--></script><noscript><a href='http://$host/delivery/ck.php?n=$uniqid&amp;cb=$rand' target='_blank'><img src='http://$host/delivery/avw.php?zoneid=$zoneId&amp;cb=$rand&amp;n=$uniqid' border='0' alt='' /></a></noscript>

EOF;
		return $html;
	}
}
