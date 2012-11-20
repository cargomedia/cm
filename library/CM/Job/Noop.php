<?php

class CM_Job_Noop extends CM_Job_Abstract {

	protected function _run(CM_Params $params) {
		return null;
	}

}