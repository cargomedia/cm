<?php

class CM_SVM_ModelTest extends CMTest_TestCase {
	/**
	 * @var CM_SVM_Model
	 */
	private $_svm;

	public function setUp() {
		if (!extension_loaded('svm')) {
			$this->markTestSkipped('Extension `svm` not loaded.');
		}
		$this->_svm = new CM_SVM_Model(1);
	}

	public function tearDown() {
		if ($this->_svm) {
			$this->_svm->flush();
		}
	}

	public function testFlush() {
		$this->_svm->addTraining(-1, array(1 => 1.0, 2 => 0.0));
		$this->_svm->addTraining(1, array(1 => 0.0, 2 => 1.0));
		$this->_svm->train();
		$this->assertSame(1, $this->_svm->predict(array(1 => 0.0, 2 => 1.0)));
		$this->_svm->flush();
		$this->assertNotSame(1, $this->_svm->predict(array(1 => 0.0, 2 => 1.0)));
	}

	public function testPredict() {
		$this->_svm->addTraining(-1, array(1 => 0.43, 3 => 0.12, 9284 => 0.2));
		$this->_svm->addTraining(1, array(1 => 0.22, 5 => 0.01, 94 => 0.11));
		$this->_svm->train();
		$this->assertSame(-1, $this->_svm->predict(array(1 => 0.43, 3 => 0.12, 9284 => 0.2)));
	}

	public function testPredictUnbalanced() {
		$this->_svm->addTraining(-1, array(1 => 1.0, 2 => 0.0));
		$this->_svm->addTraining(-1, array(1 => 1.0, 2 => 0.0));
		$this->_svm->addTraining(-1, array(1 => 1.0, 2 => 0.0));
		$this->_svm->addTraining(-1, array(1 => 1.0, 2 => 0.0));
		$this->_svm->addTraining(-1, array(1 => 0.5, 2 => 0.5));
		$this->_svm->addTraining(-1, array(1 => 0.5, 2 => 0.5));
		$this->_svm->addTraining(-1, array(1 => 0.5, 2 => 0.5));
		$this->_svm->addTraining(-1, array(1 => 0.5, 2 => 0.5));
		$this->_svm->addTraining(-1, array(1 => 0.5, 2 => 0.5));
		$this->_svm->addTraining(-1, array(1 => 0.5, 2 => 0.5));
		$this->_svm->addTraining(-1, array(1 => 0.5, 2 => 0.5));
		$this->_svm->addTraining(-1, array(1 => 0.5, 2 => 0.5));
		$this->_svm->addTraining(1, array(1 => 0.0, 2 => 1.0));
		$this->_svm->addTraining(1, array(1 => 0.0, 2 => 1.0));
		$this->_svm->train();
		$this->assertSame(1, $this->_svm->predict(array(1 => 0.0, 2 => 1.0)));
	}

	public function testPredictMulti() {
		$this->_svm->addTraining(1, array(1 => 1, 2 => 0, 3 => 0));
		$this->_svm->addTraining(1, array(1 => 1, 2 => 0, 3 => 0));
		$this->_svm->addTraining(1, array(1 => 1, 2 => 0, 3 => 0));
		$this->_svm->addTraining(2, array(1 => 0, 2 => 1, 3 => 0));
		$this->_svm->addTraining(2, array(1 => 0, 2 => 1, 3 => 0));
		$this->_svm->addTraining(2, array(1 => 0, 2 => 1, 3 => 0));
		$this->_svm->addTraining(3, array(1 => 0, 2 => 0, 3 => 1));
		$this->_svm->addTraining(3, array(1 => 0, 2 => 0, 3 => 1));
		$this->_svm->addTraining(3, array(1 => 0, 2 => 0, 3 => 1));
		$this->_svm->train();
		$this->assertSame(1, $this->_svm->predict(array(1 => 1, 2 => 0, 3 => 0)));
		$this->assertSame(2, $this->_svm->predict(array(1 => 0, 2 => 1, 3 => 0)));
		$this->assertSame(3, $this->_svm->predict(array(1 => 0, 2 => 0, 3 => 1)));
	}

	public function testPredictChange() {
		$this->_svm->addTraining(1, array(1 => 0.5, 2 => 0.8, 3 => 0.0, 4 => 0.1));
		$this->_svm->addTraining(2, array(1 => 0.1, 2 => 0.2, 3 => 0.8, 4 => 0.6));
		$this->_svm->train();
		$this->assertSame(2, $this->_svm->predict(array(1 => 0.0, 2 => 0.0, 3 => 1.0, 4 => 1.0)));

		$this->_svm->addTraining(1, array(1 => 0.0, 2 => 0.0, 3 => 1.0, 4 => 1.0));
		$this->_svm->addTraining(1, array(1 => 0.0, 2 => 0.0, 3 => 1.0, 4 => 1.0));
		$this->_svm->addTraining(1, array(1 => 0.0, 2 => 0.0, 3 => 1.0, 4 => 1.0));
		$this->assertSame(2, $this->_svm->predict(array(1 => 0.0, 2 => 0.0, 3 => 1.0, 4 => 1.0)));
		$this->_svm->train();
		$this->assertSame(1, $this->_svm->predict(array(1 => 0.0, 2 => 0.0, 3 => 1.0, 4 => 1.0)));
	}

	public function testTrainChanged() {
		$svm = new CM_SVM_Model(1);
		$svm->addTraining(-1, array(1 => 1.0, 2 => 0.0));
		$svm->addTraining(1, array(1 => 0.0, 2 => 1.0));
		$this->assertNotSame(1, $svm->predict(array(1 => 0.0, 2 => 1.0)));

		CM_SVM_Model::trainChanged();
		$svm = new CM_SVM_Model(1);
		$this->assertSame(1, $svm->predict(array(1 => 0.0, 2 => 1.0)));

		$svm->flush();
	}

	public function testDeleteOldTrainings() {
		$svm = new CM_SVM_Model(1);
		$svm->addTraining(-1, array(1 => 1.0, 2 => 0.0));
		$svm->addTraining(-1, array(1 => 1.0, 2 => 0.0));
		$svm->addTraining(1, array(1 => 0.0, 2 => 1.0));
		$svm->addTraining(1, array(1 => 0.0, 2 => 1.0));
		$svm->train();
		$this->assertSame(1, $svm->predict(array(1 => 0.0, 2 => 1.0)));

		$svm->addTraining(1, array(1 => 1.0, 2 => 0.0));
		$svm->addTraining(-1, array(1 => 0.0, 2 => 1.0));
		$svm->train();
		$this->assertNotSame(-1, $svm->predict(array(1 => 0.0, 2 => 1.0)));

		CM_SVM_Model::deleteOldTrainings(2);
		$svm = new CM_SVM_Model(1);
		$svm->train();
		$this->assertSame(-1, $svm->predict(array(1 => 0.0, 2 => 1.0)));

		$svm->flush();
	}

}
