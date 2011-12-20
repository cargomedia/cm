<?php

abstract class CM_Paging_Transgression_Abstract extends CM_Paging_Abstract {

	/**
	 * @param CM_Action_Abstract $action
	 * @param int $limitType
	 */
	abstract public function add(CM_Action_Abstract $action, $limitType);
}
