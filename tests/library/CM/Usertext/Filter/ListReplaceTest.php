<?php

class CM_Usertext_Filter_ListReplaceTest extends CMTest_TestCase {

    public function testIsMatch() {
        $filter = new CM_Usertext_Filter_ListReplace(new ArrayIterator(['bad.com', 'superbad', 'foo*bar', '|zoo|']), '…');
        $this->assertTrue($filter->isMatch('bad.com'));
        $this->assertTrue($filter->isMatch('BAD.com'));
        $this->assertTrue($filter->isMatch('sub.bad.com'));
        $this->assertTrue($filter->isMatch('bad.com-foo.de'));
        $this->assertFalse($filter->isMatch('evil.com'));
        $this->assertTrue($filter->isMatch('foo-bar'));
        $this->assertTrue($filter->isMatch('foo"bar'));
        $this->assertTrue($filter->isMatch('test zoo test'));
        $this->assertFalse($filter->isMatch('testzoo'));
        $this->assertFalse($filter->isMatch('testzootest'));
        $this->assertFalse($filter->isMatch('test zootest'));
        $this->assertFalse($filter->isMatch('foo&quot;bar'));

        $filter = new CM_Usertext_Filter_ListReplace(new ArrayIterator(['bad.com', 'superbad', 'foo*bar', '|zoo|', 'evil.com']), '…');
        $this->assertTrue($filter->isMatch('evil.com'));
    }

    public function testGetMatch() {
        $filter = new CM_Usertext_Filter_ListReplace(new ArrayIterator(['bad.com', 'superbad', 'foo*bar', '|zoo|']), '…');
        $this->assertSame('bad.com', $filter->getMatch('bad.com'));
        $this->assertSame('bad.com', $filter->getMatch('BAD.com'));
        $this->assertSame('bad.com', $filter->getMatch('sub.bad.com'));
        $this->assertSame('bad.com', $filter->getMatch('bad.com-foo.de'));
        $this->assertFalse($filter->getMatch('evil.com'));
        $this->assertSame('foobar', $filter->getMatch('foo-bar'));
        $this->assertSame('foobar', $filter->getMatch('hallo foo-bar world.'));
        $this->assertSame('zoo', $filter->getMatch('test zoo test'));
        $this->assertFalse($filter->getMatch('testzoo'));
        $this->assertFalse($filter->getMatch('testzootest'));
        $this->assertFalse($filter->getMatch('test zootest'));
    }

    public function testTransform() {
        $replace = '…';
        $filter = new CM_Usertext_Filter_ListReplace(new ArrayIterator([]), $replace);
        $render = new CM_Frontend_Render();

        $actual = $filter->transform("hello foo there", $render);
        $this->assertSame("hello foo there", $actual);

        $filter = new CM_Usertext_Filter_ListReplace(new ArrayIterator(['foo', 'f(o-].)o', 'bar', 'foobar', 'zoo*far']), $replace);

        $actual = $filter->transform("hello foo there", $render);
        $this->assertSame("hello ${replace} there", $actual);
        $actual = $filter->transform("hello Foo there", $render);
        $this->assertSame("hello ${replace} there", $actual);
        $actual = $filter->transform("hello foot there", $render);
        $this->assertSame("hello ${replace} there", $actual);

        $actual = $filter->transform("hello f(o-].)o there", $render);
        $this->assertSame("hello ${replace} there", $actual);

        $actual = $filter->transform("hello bar there", $render);
        $this->assertSame("hello ${replace} there", $actual);
        $actual = $filter->transform("hello bart there", $render);
        $this->assertSame("hello ${replace} there", $actual);
        $actual = $filter->transform("hello bar3 there", $render);
        $this->assertSame("hello ${replace} there", $actual);
        $actual = $filter->transform("hello bartender there", $render);
        $this->assertSame("hello ${replace} there", $actual);
        $actual = $filter->transform("hello bar.de there", $render);
        $this->assertSame("hello ${replace} there", $actual);
        $actual = $filter->transform("hello bar. there", $render);
        $this->assertSame("hello ${replace} there", $actual);

        $actual = $filter->transform("hello foobar there", $render);
        $this->assertSame("hello ${replace} there", $actual);
        $actual = $filter->transform("hello XfoobarX there", $render);
        $this->assertSame("hello ${replace} there", $actual);
        $actual = $filter->transform("hello mayo.foobar.ran there", $render);
        $this->assertSame("hello ${replace} there", $actual);

        $actual = $filter->transform("hello zoofar there", $render);
        $this->assertSame("hello ${replace} there", $actual);

        $actual = $filter->transform("hello zoo!!far there", $render);
        $this->assertSame("hello ${replace} there", $actual);
        $actual = $filter->transform("hello zoo far there", $render);
        $this->assertSame("hello ${replace} there", $actual);
    }

    public function testTransformReplaceCapture() {
        $render = new CM_Frontend_Render();
        $replace = '<<$0>>';
        $filter = new CM_Usertext_Filter_ListReplace(new ArrayIterator(['foo', 'f(o-].)o', 'bar', 'foobar', 'zoo*far']), $replace);

        $actual = $filter->transform("hello foo there", $render);
        $this->assertSame("hello <<foo>> there", $actual);
        $actual = $filter->transform("hello Foo there", $render);
        $this->assertSame("hello <<Foo>> there", $actual);
        $actual = $filter->transform("hello foot there", $render);
        $this->assertSame("hello <<foot>> there", $actual);
        $actual = $filter->transform("hello f(o-].)o there", $render);
        $this->assertSame("hello <<f(o-].)o>> there", $actual);
    }
}
