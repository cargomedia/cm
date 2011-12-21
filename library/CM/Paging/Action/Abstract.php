<?php

abstract class CM_Paging_Action_Abstract extends CM_Paging_Abstract {

	/**
	 * @param CM_Action_Abstract $action
	 */
	abstract public function add(CM_Action_Abstract $action);
}
