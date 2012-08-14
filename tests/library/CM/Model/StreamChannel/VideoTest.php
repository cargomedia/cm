<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Model_StreamChannel_VideoTest extends TestCase {

	public function tearDown() {
		TH::clearEnv();
	}

	public function testCreate() {
		/** @var CM_Model_StreamChannel_Video $channel */
		$channel = CM_Model_StreamChannel_Video::create(array('key' => 'foo', 'width' => 100, 'height' => 200, 'wowzaIp' => ip2long('127.0.0.1')));
		$this->assertInstanceOf('CM_Model_StreamChannel_Video', $channel);
		$this->assertSame(100, $channel->getWidth());
		$this->assertSame(200, $channel->getHeight());
		$this->assertSame('127.0.0.1', long2ip($channel->getWowzaIp()));
		$this->assertSame('foo', $channel->getKey());
	}

	public function testArchive() {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = TH::createStreamChannel();
		$streamPublish = TH::createStreamPublish(TH::createUser(), $streamChannel);
		try {
			new CM_Model_StreamChannelArchive_Video($streamChannel->getId());
			$this->fail('Can instantiate not yet existent archive.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
		$end = time();
		$streamChannel->archive();
		$archive = new CM_Model_StreamChannelArchive_Video($streamChannel->getId());
		$this->assertModelEquals($streamPublish->getUser(), $archive->getUser());
		$this->assertSame($streamChannel->getWidth(), $archive->getWidth());
		$this->assertSame($streamChannel->getHeight(), $archive->getHeight());
		$this->assertSame($end - $streamPublish->getStart(), $archive->getDuration());
		$this->assertSame($streamPublish->getStart(), $archive->getCreated());
	}

	public function testDelete() {
		$streamChannel = TH::createStreamChannel();
		$streamPublish = TH::createStreamPublish(TH::createUser(), $streamChannel);
		$mock = $this->getMock('CM_Model_StreamChannel_Video', array('archive'), array($streamChannel->getId()), 'CM_Model_StreamChannel_VideoMock');
		$mock->expects($this->once())->method('archive');
		$mock->delete();
	}
}

