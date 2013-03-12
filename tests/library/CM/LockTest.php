<?php

class CM_LockTest extends CMTest_TestCase {

	public function testLock() {
		$lock = new CM_Lock('lock-test');
		$this->assertFalse($lock->isLocked());
		$lock->lock(1);
		$this->assertTrue($lock->isLocked());
	}

	public function testUnlock() {
		$lock = new CM_Lock('unlock-test');
		$lock->lock();
		$this->assertTrue($lock->isLocked());
		$lock->unlock();
		$this->assertFalse($lock->isLocked());
	}

	public function testWaitUntilUnlocked() {
		$lock = new CM_Lock('wait-test');
		$lockedAt = time();
		$lock->lock(30);
		CMTest_TH::timeForward(30);
		$lock->waitUntilUnlocked();
		$this->assertSameTime($lockedAt + 30, time());
	}
}
