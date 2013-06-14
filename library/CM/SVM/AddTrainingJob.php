<?php

class CM_SVM_AddTrainingJob extends CM_Jobdistribution_Job_Abstract {

	protected function _execute(CM_Params $params) {
		$id = $params->getInt('id');
		$class = $params->getInt('class');
		$values = $params->getArray('values');

		$svm = new CM_SVM_Model($id);
		$svm->addTraining($class, $values);
	}

}
