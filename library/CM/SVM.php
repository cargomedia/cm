<?php

class CM_SVM {
	/**
	 * @var SVMModel
	 */
	private $_model;

	/**
	 * @var int
	 */
	private $_id;

	/**
	 * @param int $id
	 */
	public function __construct($id) {
		if (!extension_loaded('svm')) {
			throw new CM_Exception('Extension `svm` not loaded.');
		}
		$this->_id = (int) $id;
		if (!file_exists($this->_getPath())) {
			$this->train();
		}
		$this->_model = new SVMModel($this->_getPath());
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * @param int $class
	 * @param array $values Feature=>Value pairs
	 */
	public function addTraining($class, array $values) {
		$class = (int) $class;
		$values = $this->_parseValues($values);
		CM_Mysql::insert(TBL_CM_SVMTRAINING,
				array('svmId' => $this->getId(), 'class' => $class, 'values' => serialize($values), 'createStamp' => time()));
		CM_Mysql::replace(TBL_CM_SVM, array('id' => $this->getId(), 'trainingChanges' => 1));
	}

	/**
	 * @param array $values Feature=>Value pairs
	 * @return int
	 */
	public function predict(array $values) {
		$values = $this->_parseValues($values);
		$result = $this->_model->predict($values);
		return (int) $result;
	}

	/**
	 * @param boolean $autoWeight OPTIONAL Clone trainings, so that every class has the same amount of trainings
	 */
	public function train($autoWeight = true) {
		$svm = new SVM();
		$trainings = CM_Mysql::select(TBL_CM_SVMTRAINING, array('class', 'values'), array('svmId' => $this->getId()))->fetchAll();
		$classTrainings = array();
		foreach ($trainings as $training) {
			if (!isset($classTrainings[$training['class']])) {
				$classTrainings[$training['class']] = array();
			}
			$classTrainings[$training['class']][] = unserialize($training['values']);
		}

		$classCountMax = 0;
		foreach ($classTrainings as $class => $valueSets) {
			$classCountMax = max($classCountMax, count($valueSets));
		}

		$problem = array();
		foreach ($classTrainings as $class => $valueSets) {
			while ($autoWeight && count($valueSets) < $classCountMax) {
				$valueSets = array_merge($valueSets, array_slice($valueSets, -0, ($classCountMax - count($valueSets))));
			}
			foreach ($valueSets as $values) {
				$problem[] = array_merge(array(0 => $class), $values);
			}
		}

		$this->_model = $svm->train($problem);
		$this->_model->save($this->_getPath());
		CM_Mysql::replace(TBL_CM_SVM, array('id' => $this->getId(), 'trainingChanges' => 0));
	}

	public function flush() {
		CM_Mysql::delete(TBL_CM_SVMTRAINING, array('svmId' => $this->getId()));
		CM_Mysql::replace(TBL_CM_SVM, array('id' => $this->getId(), 'trainingChanges' => 1));
		$file = new CM_File($this->_getPath());
		$file->delete();
		$this->__construct($this->_id);
	}

	/**
	 * @return string
	 */
	private function _getPath() {
		$basePath = DIR_DATA . 'svm' . DIRECTORY_SEPARATOR;
		if (!is_dir($basePath)) {
			CM_Util::mkDir($basePath);
		}
		return $basePath . $this->getId() . '.svm';
	}

	/**
	 * @param array $values
	 * @return array
	 */
	private function _parseValues(array $values) {
		ksort($values);
		$values = array_values($values);
		if (isset($values[0])) {
			// Cannot have feature `0`
			$values[] = $values[0];
			unset($values[0]);
		}
		foreach ($values as $feature => &$value) {
			// Values between 0 and 1
			$value = (float) max(0, min(1, $value));
		}
		ksort($values);
		return $values;
	}

	/**
	 * @param int $trainingsMax
	 */
	public static function deleteOldTrainings($trainingsMax) {
		$trainingsMax = (int) $trainingsMax;
		$ids = CM_Mysql::select(TBL_CM_SVM, 'id')->fetchCol();
		foreach ($ids as $id) {
			$trainingsCount = CM_Db_Db::count(TBL_CM_SVMTRAINING, array('svmId' => $id));
			if ($trainingsCount > $trainingsMax) {
				$deletedCount = CM_Mysql::exec(
						'DELETE FROM TBL_CM_SVMTRAINING WHERE `svmId`=' . $id . ' ORDER BY `createStamp` LIMIT ' . ($trainingsCount - $trainingsMax));
				if ($deletedCount > 0) {
					CM_Mysql::replace(TBL_CM_SVM, array('id' => $id, 'trainingChanges' => 1));
				}
			}
		}
	}

	public static function trainChanged() {
		$ids = CM_Mysql::select(TBL_CM_SVM, 'id', array('trainingChanges' => 1))->fetchCol();
		foreach ($ids as $id) {
			$svm = new self($id);
			$svm->train();
		}
	}

}
