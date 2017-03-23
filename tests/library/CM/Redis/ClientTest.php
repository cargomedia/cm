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

    public function testLTrim() {
        $this->_client->lPush('foo', 'bar1');
        $this->_client->lPush('foo', 'bar2');
        $this->_client->lPush('foo', 'bar3');

        $this->_client->lTrim('foo', 1, 1);
        $this->assertSame(array('bar2'), $this->_client->lRange('foo'));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage does not contain a list
     */
    public function testLLenNotList() {
        $this->_client->zAdd('foo', 2, 'bar');
        $this->_client->lLen('foo');
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage does not contain a list
     */
    public function testLTrimNotList() {
        $this->_client->zAdd('foo', 2, 'bar');
        $this->_client->lTrim('foo', 1, 1);
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
        $this->assertSame(array('foo', 'bar', 'foobar'), $this->_client->zRangeByScore($key, '1', '2'));
        // count
        $this->assertSame(array('foo', 'bar'), $this->_client->zRangeByScore($key, '1', '2', 2));
        // offset
        $this->assertSame(array('bar', 'foobar'), $this->_client->zRangeByScore($key, '1', '2', null, 1));
        // withscores
        $this->assertSame(array('foo' => '1', 'bar' => '1.5', 'foobar' => '2'), $this->_client->zRangeByScore($key, '1', '2', null, null, true));
        $this->assertSame(array(), $this->_client->zRangeByScore($key, '1', '2', 0, 0));
    }

    public function testZRem() {
        $key = 'foo';
        $this->_client->zAdd($key, 1, 'foo');
        $this->_client->zAdd($key, 1.5, 'bar');
        $this->_client->zAdd($key, 2, 'foobar');
        $this->assertSame(array('foo', 'bar', 'foobar'), $this->_client->zRangeByScore($key, '1', '2'));
        $this->_client->zRem($key, 'bar');
        $this->assertSame(array('foo', 'foobar'), $this->_client->zRangeByScore($key, '1', '2'));
    }

    public function testZRemRangeByScore() {
        $key = 'foo';
        $this->_client->zAdd($key, 1, 'foo');
        $this->_client->zAdd($key, 1.5, 'bar');
        $this->_client->zAdd($key, 2, 'foobar');
        $this->assertSame(array('foo', 'bar', 'foobar'), $this->_client->zRangeByScore($key, '1', '2'));
        $this->_client->zRemRangeByScore($key, '1.2', '1.8');
        $this->assertSame(array('foo', 'foobar'), $this->_client->zRangeByScore($key, '1', '2'));
    }

    public function testZPopRangeByScore() {
        $key = 'foo';
        $this->_client->zAdd($key, 1, 'foo');
        $this->_client->zAdd($key, 1.5, 'bar');
        $this->_client->zAdd($key, 2, 'foobar');
        $this->assertSame(array('foo', 'bar', 'foobar'), $this->_client->zRangeByScore($key, '1', '2'));
        $removedValues = $this->_client->zPopRangeByScore($key, '1.2', '1.8');
        $this->assertSame(array('bar'), $removedValues);
        $this->assertSame(2, $this->_client->zCard($key));

        $removedValues = $this->_client->zPopRangeByScore($key, '0.8', '1.2', true);
        $this->assertSame(array('foo', '1'), $removedValues);
        $this->assertSame(1, $this->_client->zCard($key));
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
        $members = $this->_client->sFlush('foo');
        sort($members);
        $this->assertSame(['bar1', 'bar2', 'bar3'], $members);
        $this->assertSame(0, $this->_client->sCard('foo'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPubSub() {
        if (0 === ($pid = pcntl_fork())) {
            // Child process
            $redisClient = CM_Service_Manager::getInstance()->getRedis();
            $clientCallCount = 0;
            while (1 >= $clientCallCount) {
                usleep(50 * 1000);
                if (0 != $redisClient->publish('foo', 'bar' . $clientCallCount)) {
                    $clientCallCount++;
                }
            }
            exit;
        }

        $messageList = [];
        $messageCount = 0;
        $response = $this->_client->subscribe('foo', function ($channel, $message) use (&$messageList, &$messageCount) {
            $messageList[] = $message;
            if (2 <= ++$messageCount) {
                return [$channel, $messageList];
            }
            return null;
        });
        $this->assertSame(['foo', ['bar0', 'bar1']], $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPubSubWithClientUsedInSubClosure() {
        if (0 === ($pid = pcntl_fork())) {
            // Child process
            $redisClient = CM_Service_Manager::getInstance()->getRedis();
            $clientCount = 0;
            while ($clientCount == 0) {
                usleep(50 * 1000);
                $clientCount = $redisClient->publish('foo', 'test');
            }
            exit;
        }

        $this->_client->set('check', 'bar');
        $response = $this->_client->subscribe('foo', function ($channel, $message) {
            if ($message == 'test') {
                return [$channel, $message, $this->_client->get('check')];
            } else {
                return false;
            }
        });
        $this->assertSame(['foo', 'test', 'bar'], $response);
    }
}
