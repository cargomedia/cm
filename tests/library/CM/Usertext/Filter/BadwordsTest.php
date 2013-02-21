<?php

class CM_Usertext_Filter_BadwordsTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$replace = '…';
		$badwords = new CM_Paging_ContentList_Badwords();
		$badwords->add('foo');
		$badwords->add('f(o-].)o');
		$badwords->add('bar*');
		$badwords->add('*foobar*');
		$badwords->add('*zoo*far*');
		CMTest_TH::clearCache();

		$filter = new CM_Usertext_Filter_Badwords();

		$actual = $filter->transform("hello foo there");
		$this->assertEquals("hello ${replace} there", $actual);
		$actual = $filter->transform("hello Foo there");
		$this->assertEquals("hello ${replace} there", $actual);
		$actual = $filter->transform("hello foot there");
		$this->assertEquals("hello foot there", $actual);

		$actual = $filter->transform("hello f(o-].)o there");
		$this->assertEquals("hello ${replace} there", $actual);

		$actual = $filter->transform("hello bar there");
		$this->assertEquals("hello ${replace} there", $actual);
		$actual = $filter->transform("hello bart there");
		$this->assertEquals("hello ${replace} there", $actual);
		$actual = $filter->transform("hello bar3 there");
		$this->assertEquals("hello ${replace} there", $actual);
		$actual = $filter->transform("hello bartender there");
		$this->assertEquals("hello ${replace} there", $actual);
		$actual = $filter->transform("hello bar.de there");
		$this->assertEquals("hello ${replace} there", $actual);
		$actual = $filter->transform("hello bar. there");
		$this->assertEquals("hello ${replace}. there", $actual);

		$actual = $filter->transform("hello foobar there");
		$this->assertEquals("hello ${replace} there", $actual);
		$actual = $filter->transform("hello XfoobarX there");
		$this->assertEquals("hello ${replace} there", $actual);
		$actual = $filter->transform("hello mayo.foobar.ran there");
		$this->assertEquals("hello ${replace} there", $actual);

		$actual = $filter->transform("hello zoofar there");
		$this->assertEquals("hello ${replace} there", $actual);

		$actual = $filter->transform("hello zoo!!far there");
		$this->assertEquals("hello ${replace} there", $actual);
		$actual = $filter->transform("hello zoo far there");
		$this->assertEquals("hello zoo far there", $actual);
	}

}
