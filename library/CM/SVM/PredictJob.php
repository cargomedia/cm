<?php

class CM_SVM_PredictJob extends CM_Jobdistribution_Job_Abstract {

	protected function _run(CM_Params $params) {
		$id = $params->getInt('id');
		$values = $params->getArray('values');
		$svm = new CM_SVM_Model($id);
		return $svm->predict($values);
	}

}
