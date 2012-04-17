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
background:url($url) no-repeat 66px 7px;

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

	public function testOpacity() {
		$css = <<<'EOD'
.foo {
	filter:hello(world);
	.opacity(.3);
}
.bar {
	.opacity(foo);
}
EOD;
		$expected = <<<'EOD'
.foo {
  filter:hello(world);
  opacity:.3;
  filter:alpha(opacity=30);
}

EOD;
		$css = new CM_Css($css);
		$this->assertEquals($expected, $css->compile(CM_Render::getInstance()));
	}

	public function testLinearGradient() {
		//horizontal
		$css = <<<'EOD'
.foo {
	.gradient(horizontal, #000000, rgba(30, 50,30, 0.4));
}
EOD;
		$expected = <<<'EOD'
.foo {
  filter:progid:DXImageTransform.Microsoft.gradient(GradientType=1,startColorstr=#ff000000,endColorstr=#661e321e);
  background-image:linear-gradient(left,#000000,rgba(30,50,30,0.4));
  background-image:-moz-linear-gradient(left,#000000,rgba(30,50,30,0.4));
  background-image:-webkit-linear-gradient(left,#000000,rgba(30,50,30,0.4));
  background-image:-o-linear-gradient(left,#000000,rgba(30,50,30,0.4));
  background-image:-ms-linear-gradient(left,#000000,rgba(30,50,30,0.4));
}

EOD;
		$css = new CM_Css($css);
		$this->assertSame($expected, $css->compile(CM_Render::getInstance()));
		//vertical
		$css = <<<'EOD'
.foo {
	.gradient(vertical, #000000, rgba(30, 50,30, 0.4));
}
EOD;
		$expected = <<<'EOD'
.foo {
  filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=#ff000000,endColorstr=#661e321e);
  background-image:linear-gradient(top,#000000,rgba(30,50,30,0.4));
  background-image:-moz-linear-gradient(top,#000000,rgba(30,50,30,0.4));
  background-image:-webkit-linear-gradient(top,#000000,rgba(30,50,30,0.4));
  background-image:-o-linear-gradient(top,#000000,rgba(30,50,30,0.4));
  background-image:-ms-linear-gradient(top,#000000,rgba(30,50,30,0.4));
}

EOD;
		$css = new CM_Css($css);
		$this->assertSame($expected, $css->compile(CM_Render::getInstance()));
		//illegal parameters
		$css = <<<'EOD'
.foo {
	.gradient(vertical, foo, rgba(30, 50,30, 0.4));
	.gradient(vertical, #000000, foo);
	.gradient(horizontal, foo, rgba(30, 50,30, 0.4));
	.gradient(horizontal, #000000, foo);
	.gradient(foo, #000000, rgba(30, 50,30, 0.4));
}
EOD;
		$css = new CM_Css($css);
		$this->assertSame('', $css->compile(CM_Render::getInstance()));
	}

	public function testBackgroundColor() {
		$css = <<<'EOD'
.foo {
	.background-color(rgba(1,1,1,0.5));
}
.bar {
	.background-color(rgba(1,1,1,1));
}
EOD;
		$expected = <<<'EOD'
.foo {
  filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=#7f010101,endColorstr=#7f010101);
  background-color:rgba(1,1,1,0.5);
}
.bar { background-color:#010101; }

EOD;
		$css = new CM_Css($css);
		$this->assertSame($expected, $css->compile(CM_Render::getInstance()));

		//illegal value
		$css = <<<'EOD'
.foo {
	.background-color(123);
}
EOD;
		$css = new CM_Css($css);
		$this->assertSame('', $css->compile(CM_Render::getInstance()));

	}

	public function testBoxShadow() {
		$css = <<<'EOD'
.foo {
	.box-shadow(0 0 2px #dddddd);
}
EOD;
		$expected = <<<'EOD'
.foo {
  box-shadow:0 0 2px #dddddd;
  -webkit-box-shadow:0 0 2px #dddddd;
}

EOD;
		$css = new CM_Css($css);
		$this->assertSame($expected, $css->compile(CM_Render::getInstance()));
	}

	public function testBoxSizing() {
		$css = <<<'EOD'
.foo {
	.box-sizing(border-box);
}
EOD;
		$expected = <<<'EOD'
.foo {
  box-sizing:border-box;
  -moz-box-sizing:border-box;
  -webkit-box-sizing:border-box;
}

EOD;
		$css = new CM_Css($css);
		$this->assertSame($expected, $css->compile(CM_Render::getInstance()));
	}

	public function testUserSelect() {
		$css = <<<'EOD'
.foo {
	.user-select(none);
}
EOD;
		$expected = <<<'EOD'
.foo {
  user-select:none;
  -moz-user-select:none;
  -ms-user-select:none;
  -webkit-user-select:none;
}

EOD;
		$css = new CM_Css($css);
		$this->assertSame($expected, $css->compile(CM_Render::getInstance()));
	}

	public function testTransform() {
		$css = <<<'EOD'
.foo {
	.transform(matrix(0.866,0.5,-0.5,0.866,0,0));
}
EOD;
		$expected = <<<'EOD'
.foo {
  transform:matrix(0.866,0.5,-0.5,0.866,0,0);
  -moz-transform:matrix(0.866,0.5,-0.5,0.866,0,0);
  -o-transform:matrix(0.866,0.5,-0.5,0.866,0,0);
  -ms-transform:matrix(0.866,0.5,-0.5,0.866,0,0);
  -webkit-transform:matrix(0.866,0.5,-0.5,0.866,0,0);
}

EOD;
		$css = new CM_Css($css);
		$this->assertSame($expected, $css->compile(CM_Render::getInstance()));
	}

	public function testTransition() {
		$css = <<<'EOD'
.foo {
	.transition(width 2s ease-in 2s);
}
EOD;
		$expected = <<<'EOD'
.foo {
  transition:width 2s ease-in 2s;
  -moz-transition:width 2s ease-in 2s;
  -webkit-transition:width 2s ease-in 2s;
}

EOD;
		$css = new CM_Css($css);
		$this->assertSame($expected, $css->compile(CM_Render::getInstance()));
	}
}
