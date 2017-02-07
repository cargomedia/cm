<?php

class CM_Http_Request_PostTest extends CMTest_TestCase {

    public function testSanitize() {
        $malformedString = pack("H*", 'c32e');
        $request = new CM_Http_Request_Post('http://foo.bar?baz=fooBar', null, null, '{ "foo" : "' . $malformedString . '" }');
        $query = $request->getQuery();
        $this->assertSame('fooBar', $query['baz']);
        $this->assertArrayHasKey('foo', $query);
    }
}
