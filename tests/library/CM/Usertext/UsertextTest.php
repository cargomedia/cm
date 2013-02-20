<?php

class CM_UsertextTest extends CMTest_TestCase {

	private static $_smileySetId;

	private static $_smileyIds = array();

	private $_text = <<<EOD
smilies: :-) :smiley:
badwords: hallo@yahoo.com
#Headline#
EOD;

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public static function setUpBeforeClass() {
		$badwords = new CM_Paging_ContentList_Badwords();
		$badwords->add('@yahoo.com');

		self::$_smileySetId = $setId = CM_Mysql::insert(TBL_CM_SMILEYSET, array('label' => 'testSet'));
		self::$_smileyIds[] = CM_Mysql::insert(TBL_CM_SMILEY, array('setId' => $setId, 'code' => ':),:-),:smiley:', 'file' => '1.png'));
		self::$_smileyIds[] = CM_Mysql::insert(TBL_CM_SMILEY, array('setId' => $setId, 'code' => ';)', 'file' => '2.png'));
		self::$_smileyIds[] = CM_Mysql::insert(TBL_CM_SMILEY, array('setId' => $setId, 'code' => ':(,:-(', 'file' => '3.png'));
		self::$_smileyIds[] = CM_Mysql::insert(TBL_CM_SMILEY, array('setId' => $setId, 'code' => ':PLAYMATE:', 'file' => '4.png'));
		self::$_smileyIds[] = CM_Mysql::insert(TBL_CM_SMILEY, array('setId' => $setId, 'code' => '<3', 'file' => '5.png'));

		CMTest_TH::clearCache();
	}

	public function testMarkdown() {
		$expected = <<<EOD
<p>smilies: <img class="emoticon" title=":-)" alt=":-)" src="/img/smiley/1.png" /> <img class="emoticon" title=":smiley:" alt=":smiley:" src="/img/smiley/1.png" /></p>
<p>badwords: hallo…</p>
<h1>Headline</h1>
EOD;
		$actual = new CM_Usertext_Usertext($this->_text);
		$this->assertEquals($expected, $actual->getMarkdown());
	}

	public function testMarkdownTruncate() {
		$actual = new CM_Usertext_Usertext('Hello World');
		$this->assertEquals('<p>Hello…</p>', $actual->getMarkdown(10));
		$this->assertEquals('<p>Hello World</p>', $actual->getMarkdown(11));
		$this->assertEquals('<p>Hello World</p>', $actual->getMarkdown(12));
	}

	public function testMarkdownStripEmoticon() {
		$expected = <<<EOD
<p>smilies:</p>
<p>badwords: hallo…</p>
<h1>Headline</h1>
EOD;
		$actual = new CM_Usertext_Usertext($this->_text);
		$this->assertEquals($expected, $actual->getMarkdown(null, true));
	}

	public function testPlain() {
		$expected = <<<EOD
smilies:
badwords: hallo…
Headline
EOD;
		$actual = new CM_Usertext_Usertext($this->_text);
		$this->assertEquals($expected, $actual->getPlain());
	}

	public function testPlainTruncate() {
		$actual = new CM_Usertext_Usertext('Hello World');
		$this->assertEquals('Hello…', $actual->getPlain(10));
		$this->assertEquals('Hello World', $actual->getPlain(11));
		$this->assertEquals('Hello World', $actual->getPlain(12));
	}

	public function testPlainsPreserveParagraph() {
		$expected = <<<EOD
<p>smilies:</p>
<p>badwords: hallo…</p>
<p>Headline</p>
EOD;
		$actual = new CM_Usertext_Usertext($this->_text);
		$this->assertEquals($expected, $actual->getPlain(null, true));
	}

	public function testPlainsPreserveEmoticon() {
		$expected = <<<EOD
smilies: <img class="emoticon" title=":-)" alt=":-)" src="/img/smiley/1.png" /> <img class="emoticon" title=":smiley:" alt=":smiley:" src="/img/smiley/1.png" />
badwords: hallo…
Headline
EOD;
		$actual = new CM_Usertext_Usertext($this->_text);
		$this->assertEquals($expected, $actual->getPlain(null, null, true));
	}

	public function testBadwords() {
		$badwords = new CM_Paging_ContentList_Badwords();
		$badwords->add('@yahoo.com');

		$actual = new CM_Usertext_Usertext('hallo@yahoo.com world');
		$this->assertEquals('<p>hallo… world</p>', $actual->getMarkdown());
	}

	public function testMultibyte() {
		$expected = '繁體字';
		$actual = new CM_Usertext_Usertext('繁體字');
		$this->assertEquals($expected, $actual->getPlain());
		$this->assertEquals('<p>' . $expected . '</p>', $actual->getMarkdown());
	}

	public function testCensor() {
		$replace = '…';
		$badwords = new CM_Paging_ContentList_Badwords();
		$badwords->add('foo');
		$badwords->add('f(o-].)o');
		$badwords->add('bar*');
		$badwords->add('*foobar*');
		$badwords->add('*zoo*far*');
		CMTest_TH::clearCache();

		$actual = new CM_Usertext_Usertext("hello foo there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext_Usertext("hello Foo there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext_Usertext("hello foot there");
		$this->assertEquals("hello foot there", $actual->getPlain());

		$actual = new CM_Usertext_Usertext("hello f(o-].)o there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());

		$actual = new CM_Usertext_Usertext("hello bar there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext_Usertext("hello bart there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext_Usertext("hello bar3 there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext_Usertext("hello bartender there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext_Usertext("hello bar.de there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext_Usertext("hello bar. there");
		$this->assertEquals("hello ${replace}. there", $actual->getPlain());

		$actual = new CM_Usertext_Usertext("hello foobar there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext_Usertext("hello XfoobarX there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext_Usertext("hello mayo.foobar.ran there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());

		$actual = new CM_Usertext_Usertext("hello zoofar there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());

		$actual = new CM_Usertext_Usertext("hello zoo!!far there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext_Usertext("hello zoo far there");
		$this->assertEquals("hello zoo far there", $actual->getPlain());
	}

}
