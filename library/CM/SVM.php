<?php

class CM_SVM {

	/** @var int */
	private $_id;

	/**
	 * @param int $id
	 */
	public function __construct($id) {
		$this->_id = (int) $id;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * @param int   $class
	 * @param array $values Feature=>Value pairs
	 */
	public function addTraining($class, array $values) {
		$class = (int) $class;
		$params = array('id' => $this->getId(), 'class' => $class, 'values' => $values);
		$job = new CM_SVM_AddTrainingJob();
		$job->run($params);
	}

	/**
	 * @param array $values Feature=>Value pairs
	 * @return int
	 */
	public function predict(array $values) {
		$params = array('id' => $this->getId(), 'values' => $values);
		$job = new CM_SVM_PredictJob();
		return (int) $job->run($params);
	}
}
