<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_CssTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testToString() {
		$css = new CM_Css('color: black;', '.foo');
		$expected = <<<'EOD'
.foo {
color: black;
}

EOD;
		$this->assertSame($expected, (string) $css);
	}

	public function testAdd() {
		$css = new CM_Css('font-size: 12', '#foo');
		$css1 = <<<'EOD'
.test:visible {
	color: black;
	height:300px;
}
EOD;
		$css->add($css1, '.bar');
		$css->add('color: green;');
		$expected = <<<'EOD'
#foo {
font-size: 12
.bar {
.test:visible {
	color: black;
	height:300px;
}
}
color: green;
}

EOD;
		$this->assertSame($expected, (string) $css);
	}

	public function testImage() {
		$css = new CM_Css("background: image('icon/mailbox_read.png') no-repeat 66px 7px;");
		$render = CM_Render::getInstance();
		$url = $render->getUrlImg('icon/mailbox_read.png');
		$expected = <<<EOD
background: url($url) no-repeat 66px 7px;

EOD;
		$this->assertEquals($expected, $css->compile($render));
	}

	public function testMixin() {
		$css = <<<'EOD'
.mixin() {
	font-size:5;
	border:1px solid red;
	#bar {
		color:blue;
	}
}
.foo {
	color:red;
	.mixin;
}
EOD;
		$css = new CM_Css($css);
$expected = <<<'EOD'
.foo {
  color:red;
  font-size:5;
  border:1px solid red;
}
.foo #bar { color:blue; }

EOD;
		$this->assertEquals($expected, $css->compile(CM_Render::getInstance()));
	}
}
