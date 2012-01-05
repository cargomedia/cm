<?php

abstract class CM_Paging_Ip_Abstract extends CM_Paging_Abstract {
	/**
	 * @param int $ip
	 * @return bool
	 */
	public function contains($ip) {
		return in_array($ip, $this->getItems());
	}
}
