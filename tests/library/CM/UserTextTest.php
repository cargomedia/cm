<?php

class CM_UsertextTest extends CMTest_TestCase {

	private static $_emoticonIds = array();

	private $_text = <<<EOD
smilies: :-)
allowed tags: <b attr="not-allowed" class="italic">bold</b>
un-allowed tags: <foo>foo</foo> <big-grin> Lorem ipsum <averylongunallowedtag>hiho</averylongunallowedtag>
badwords: hallo@yahoo.com
special chars: "<>"
unclosed tags: <u>not <b>closed
EOD;

	public static function setUpBeforeClass() {
		$badwords = new CM_Paging_ContentList_Badwords();
		$badwords->add('@yahoo.com');

		self::$_emoticonIds[] = CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => ':smiley:', 'codeAdditional' => ':),:-)', 'file' => '1.png'));
		self::$_emoticonIds[] = CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => ';)', 'file' => '2.png'));
		self::$_emoticonIds[] = CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => ':(', 'codeAdditional' => ':-(', 'file' => '3.png'));
		self::$_emoticonIds[] = CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => '*PLAYMATE*', 'file' => '4.png'));
		self::$_emoticonIds[] = CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => '<3', 'file' => '5.png'));

		CMTest_TH::clearCache();
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testFormat() {
		$emoticonId = self::$_emoticonIds[0];
		$splitChar = CM_Usertext::getSplitChar();
		$expected = <<<EOD
smilies: <span class="emoticon emoticon-{$emoticonId}" title=":smiley:"></span><br />
allowed tags: <b class="italic">bold</b><br />
un-allowed tags: &lt;foo&gt;{$splitChar}foo&lt;/foo&gt; &lt;big-grin&gt; Lorem ipsum &lt;aver{$splitChar}ylongunall{$splitChar}owedtag&gt;hi{$splitChar}ho&lt;/averyl{$splitChar}ongunallow{$splitChar}edtag&gt;<br />
badwords: hallo…<br />
special chars: &quot;&lt;&gt;&quot;<br />
unclosed tags: <u>not <b>closed</b></u>
EOD;
		$actual = new CM_Usertext($this->_text);
		$this->assertEquals($expected, $actual->getFormat());
	}

	public function testBadwords() {
		$badwords = new CM_Paging_ContentList_Badwords();
		$badwords->add('@yahoo.com');

		$actual = new CM_Usertext('hallo@yahoo.com world');
		$this->assertEquals('hallo… world', $actual->getFormat());
	}

	public function testFormatPlain() {
		$emoticonId = self::$_emoticonIds[0];
		$splitChar = CM_Usertext::getSplitChar();
		$expected = <<<EOD
smilies: <span class="emoticon emoticon-{$emoticonId}" title=":smiley:"></span>
allowed tags: bold
un-allowed tags: &lt;foo&gt;{$splitChar}foo&lt;/foo&gt; &lt;big-grin&gt; Lorem ipsum &lt;aver{$splitChar}ylongunall{$splitChar}owedtag&gt;hi{$splitChar}ho&lt;/averyl{$splitChar}ongunallow{$splitChar}edtag&gt;
badwords: hallo…
special chars: &quot;&lt;&gt;&quot;
unclosed tags: not closed
EOD;
		$actual = new CM_Usertext($this->_text);
		$this->assertEquals($expected, $actual->getFormatPlain());
	}

	public function testPlain() {
		$splitChar = CM_Usertext::getSplitChar();
		$expected = <<<EOD
smilies: :-)
allowed tags: &lt;b attr=&quot;not-allowed&quot; class{$splitChar}=&quot;italic&quot;&gt;{$splitChar}bold&lt;/b&gt;
un-allowed tags: &lt;foo&gt;{$splitChar}foo&lt;/foo&gt; &lt;big-grin&gt; Lorem ipsum &lt;aver{$splitChar}ylongunall{$splitChar}owedtag&gt;hi{$splitChar}ho&lt;/averyl{$splitChar}ongunallow{$splitChar}edtag&gt;
badwords: hallo…
special chars: &quot;&lt;&gt;&quot;
unclosed tags: &lt;u&gt;not &lt;b&gt;closed
EOD;
		$actual = new CM_Usertext($this->_text);
		$this->assertEquals($expected, $actual->getPlain());
	}

	public function testPlainTruncate() {
		$actual = new CM_Usertext('Hello World');
		$this->assertEquals('Hello…', $actual->getPlain(10));
		$this->assertEquals('Hello World', $actual->getPlain(11));
		$this->assertEquals('Hello World', $actual->getPlain(12));
	}

	public function testFormatPlainTruncate() {
		$actual = new CM_Usertext('Ein Gespenst <b>geht</b> um in Europa :) test');
		$expectedEmoticon = '<span class="emoticon emoticon-' . self::$_emoticonIds[0] . '" title=":smiley:"></span>';

		$this->assertEquals('Ein Gespenst geht um in Europa ' . $expectedEmoticon . ' test', $actual->getFormatPlain(1000));
		$this->assertEquals('Ein Gespenst geht um in…', $actual->getFormatPlain(29));
		$this->assertEquals('Ein Gespenst geht um in Europa …', $actual->getFormatPlain(31));
		$this->assertEquals('Ein Gespenst geht um in Europa ' . $expectedEmoticon . '…', $actual->getFormatPlain(32));
		$this->assertEquals('Ein Gespenst geht um in Europa ' . $expectedEmoticon . '…', $actual->getFormatPlain(33));
		$this->assertEquals('Ein Gespenst geht um in Europa ' . $expectedEmoticon . ' test', $actual->getFormatPlain(37));
		$this->assertEquals('Ein Gespenst ge…', $actual->getFormatPlain(15));
		$this->assertEquals('Ein Gespenst geht um in…', $actual->getFormatPlain(25));
	}

	public function testFormatPlainTruncateEmoticon() {
		$actual = new CM_Usertext('Yo *PLAYMATE*');

		$expected = 'Yo <span class="emoticon emoticon-' . self::$_emoticonIds[3] . '" title="*PLAYMATE*"></span>';
		$this->assertEquals($expected, $actual->getFormatPlain(1000));
		$this->assertEquals($expected, $actual->getFormatPlain(4));
		$this->assertEquals('Yo ', $actual->getFormatPlain(3));
		$this->assertEquals('Yo…', $actual->getFormatPlain(2));
		$this->assertEquals('Y…', $actual->getFormatPlain(1));
	}

	public function testFormatTruncate() {
		$actual = new CM_Usertext('Anybody <u>in</u> there?');

		$this->assertEquals('Anybody <u>in</u> there?', $actual->getFormat(17));
		$this->assertEquals('Anybody <u>in</u>…', $actual->getFormat(16));
		$this->assertEquals('Anybody <u>in</u>…', $actual->getFormat(10));
		$this->assertEquals('Anybody <u>i</u>…', $actual->getFormat(9));
		$this->assertEquals('Anybody …', $actual->getFormat(8));
		$this->assertEquals('Anybody…', $actual->getFormat(7));
	}

	public function testFormatUnallowedTagsFiltering() {
		$expected =
				'<span class="emoticon emoticon-' . self::$_emoticonIds[4] . '" title="&lt;3"></span> love<br />' . PHP_EOL .
						'you';

		$actual = new CM_Usertext('<3 love' . PHP_EOL . 'you');
		$this->assertEquals($expected, $actual->getFormat());
	}

	public function testFormatAllowedTags() {
		$actual = new CM_Usertext('<b>hello</b> <u>test</u>');
		$this->assertEquals('<b>hello</b> <u>test</u>', $actual->getFormat());
		$this->assertEquals('<b>hello</b> &lt;u&gt;te​st&lt;/u&gt;', $actual->getFormat(null, array('b')));
		$this->assertEquals('&lt;b&gt;he​llo&lt;/b&gt; &lt;u&gt;te​st&lt;/u&gt;', $actual->getFormat(null, array()));
	}

	public function testFormatVisibleTags() {
		$actual = new CM_Usertext('<b>hello</b> <u>test</u>');
		$this->assertEquals('<b>hello</b> <u>test</u>', $actual->getFormat(null, array('b', 'u')));
		$this->assertEquals('<b>hello</b> test', $actual->getFormat(null, array('b', 'u'), array('b')));
	}

	public function testMultibyte() {
		$expected = '繁體字';
		$actual = new CM_Usertext('繁體字');
		$this->assertEquals($expected, $actual->getPlain());
		$this->assertEquals($expected, $actual->getFormat());
		$this->assertEquals($expected, $actual->getFormatPlain());
	}

	public function testTagEmpty() {
		$expected = '<u></u>';
		$actual = new CM_Usertext('<u></u>');
		$this->assertEquals($expected, $actual->getFormat());

		$expected = '<br />';
		$actual = new CM_Usertext('<br></br>');
		$this->assertEquals($expected, $actual->getFormat());

		$expected = '<br />test';
		$actual = new CM_Usertext('<br>test</br>');
		$this->assertEquals($expected, $actual->getFormat());
	}

	public function testNewlines() {
		$actual = new CM_Usertext("a\n\n\n\nb\nc\n");
		$this->assertEquals("a<br /><br /><br />\nb<br />\nc", $actual->getFormat());
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

		$actual = new CM_Usertext("hello foo there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext("hello Foo there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext("hello foot there");
		$this->assertEquals("hello foot there", $actual->getPlain());

		$actual = new CM_Usertext("hello f(o-].)o there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());

		$actual = new CM_Usertext("hello bar there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext("hello bart there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext("hello bar3 there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext("hello bartender there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext("hello bar.de there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext("hello bar. there");
		$this->assertEquals("hello ${replace}. there", $actual->getPlain());

		$actual = new CM_Usertext("hello foobar there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext("hello XfoobarX there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext("hello mayo.foobar.ran there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());

		$actual = new CM_Usertext("hello zoofar there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());

		$actual = new CM_Usertext("hello zoo!!far there");
		$this->assertEquals("hello ${replace} there", $actual->getPlain());
		$actual = new CM_Usertext("hello zoo far there");
		$this->assertEquals("hello zoo far there", $actual->getPlain());
	}
}
