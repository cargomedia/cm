<?php

class CM_Redis_ClientTest extends CMTest_TestCase {

    /** @var CM_Redis_Client */
    private $_client;

    public function setUp() {
        $this->_client = CM_Service_Manager::getInstance()->getRedis();
        $this->_client->flush();
    }

    public function tearDown() {
        $this->_client->flush();
    }

    public function testGetSet() {
        $this->assertSame(false, $this->_client->get('foo'));

        $this->_client->set('foo', 'bar');
        $this->assertSame('bar', $this->_client->get('foo'));
    }

    public function testExists() {
        $this->assertFalse($this->_client->exists('foo'));

        $this->_client->set('foo', 'bar');
        $this->assertTrue($this->_client->exists('foo'));
    }

    public function testRPush() {
        $this->_client->rPush('foo', 'bar1');
        $this->_client->rPush('foo', 'bar2');
        $this->assertSame(array('bar1', 'bar2'), $this->_client->lRange('foo'));
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testRPushInvalidEntry() {
        $this->_client->set('foo', 12);
        $this->_client->rPush('foo', 'bar1');
    }

    public function testLPush() {
        $this->_client->lPush('foo', 'bar1');
        $this->_client->lPush('foo', 'bar2');
        $this->assertSame(array('bar2', 'bar1'), $this->_client->lRange('foo'));
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testLPushInvalidEntry() {
        $this->_client->set('foo', 12);
        $this->_client->lPush('foo', 'bar1');
    }

    public function testRPop() {
        $this->_client->lPush('foo', 'bar');
        $this->assertSame('bar', $this->_client->rPop('foo'));
        $this->assertNull($this->_client->rPop('foo'));
    }

    public function testLLen() {
        $this->assertSame(0, $this->_client->lLen('foo'));

        $this->_client->lPush('foo', 'bar1');
        $this->_client->lPush('foo', 'bar2');
        $this->assertSame(2, $this->_client->lLen('foo'));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage does not contain a list
     */
    public function testLLenNotList() {
        $this->_client->zAdd('foo', 2, 'bar');
        $this->_client->lLen('foo');
    }

    public function testLTrim() {
        $this->_client->lPush('foo', 'bar1');
        $this->_client->lPush('foo', 'bar2');
        $this->_client->lPush('foo', 'bar3');

        $this->_client->lTrim('foo', 1, 1);
        $this->assertSame(array('bar2'), $this->_client->lRange('foo'));
    }

    public function testLRange() {
        $this->_client->rPush('foo', 'bar1');
        $this->_client->rPush('foo', 'bar2');
        $this->_client->rPush('foo', 'bar3');
        $this->assertSame(array('bar1', 'bar2', 'bar3'), $this->_client->lRange('foo'));
        $this->assertSame(array('bar2', 'bar3'), $this->_client->lRange('foo', 1));
        $this->assertSame(array('bar2'), $this->_client->lRange('foo', 1, 1));
    }

    public function testZRangeByScore() {
        $key = 'foo';
        $this->_client->zAdd($key, 1, 'foo');
        $this->_client->zAdd($key, 1.5, 'bar');
        $this->_client->zAdd($key, 2, 'foobar');
        // normal behaviour
        $this->assertSame(array('foo', 'bar', 'foobar'), $this->_client->zRangeByScore($key, 1, 2));
        // count
        $this->assertSame(array('foo', 'bar'), $this->_client->zRangeByScore($key, 1, 2, 2));
        // offset
        $this->assertSame(array('bar', 'foobar'), $this->_client->zRangeByScore($key, 1, 2, null, 1));
        // withscores
        $this->assertSame(array('foo' => '1', 'bar' => '1.5', 'foobar' => '2'), $this->_client->zRangeByScore($key, 1, 2, null, null, true));
        $this->assertSame(array(), $this->_client->zRangeByScore($key, 1, 2, 0, 0));
    }

    public function testSAdd() {
        $reply = $this->_client->sAdd('foo', 'bar');
        $this->assertSame(1, $reply);
        $this->assertSame(1, $this->_client->sCard('foo'));

        $reply = $this->_client->sAdd('foo', 'bar1');
        $this->assertSame(1, $reply);
        $this->assertSame(2, $this->_client->sCard('foo'));

        $reply = $this->_client->sAdd('foo', 'bar1');
        $this->assertSame(0, $reply);
        $this->assertSame(2, $this->_client->sCard('foo'));
    }

    public function testSRem() {
        $this->_client->sAdd('foo', 'bar');
        $this->assertSame(1, $this->_client->sCard('foo'));
        $this->_client->sRem('foo', 'bar');
        $this->assertSame(0, $this->_client->sCard('foo'));
    }

    public function testSFlush() {
        $this->_client->sAdd('foo', 'bar1');
        $this->_client->sAdd('foo', 'bar2');
        $this->_client->sAdd('foo', 'bar3');
        $this->assertSame(3, $this->_client->sCard('foo'));
        $this->_client->sFlush('foo');
        $this->assertSame(0, $this->_client->sCard('foo'));
    }

    public function testPubSub() {

        $process = CM_Process::getInstance();
        $process->fork(function () {
            $redisClient = CM_Service_Manager::getInstance()->getRedis();
            return $redisClient->subscribe('foo', function ($channel, $message) {
                return [$channel, $message];
            });
        });

        $break = false;
        $loopCount = 0;
        $retry = 40;
        $waitTime = 50 * 1000;

        // use a timeout because there's no easy way to know when the forked process will subscribe to the channel...
        while (!$break) {
            $clientCount = $this->_client->publish('foo', 'bar');
            if ($clientCount > 0) {
                $break = true;
            }
            if ($loopCount > $retry) {
                $break = true;
                $process->killChildren();
                $this->fail('Failed to publish on a Redis subpub channel after ' . round(($waitTime * $retry) / (1000 * 1000), 3) . ' second(s).');
            }
            usleep($waitTime);
            $loopCount++;
        }

        $resultList = $process->waitForChildren();
        $this->assertCount(1, $resultList);

        foreach ($resultList as $result) {
            $this->assertSame(['foo', 'bar'], $result->getResult());
        }
    }
}
