<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_CssTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testInheritance() {
		$presets = <<<'EOD'
$var {
	border: 100px;
}
EOD;
		$css = <<<'EOD'
var << $var {
}
var2 {
	border: 2px;
}
EOD;
		$expected = <<<'EOD'
var {
	border: 100px;
}
var2 {
	border: 2px;
}

EOD;
		$actual = new CM_Css($css, $this->_getRender(), new CM_Css($presets, $this->_getRender()));
		$this->assertEquals($expected, $actual->__toString());
	}

	public function testProperFormatting() {
		$presets = <<<'EOD'
$var {
	border:     100px;
	bord3r : ds;
}
EOD;
		$css = <<<'EOD'
var<<$var {
	border: 1px;
}
var2 {border: 2px;}
EOD;
		$expected = <<<'EOD'
var {
	border: 1px;
}
var2 {
	border: 2px;
}

EOD;
		$actual = new CM_Css($css, $this->_getRender(), new CM_Css($presets, $this->_getRender()));
		$this->assertEquals($expected, $actual->__toString());
	}

	public function testIllegalProperties() {
		$css = <<<'EOD'
var {
	border: 1px;
	532:88pp;
	AYAYAY: 44px;
}
EOD;
		$expected = <<<'EOD'
var {
	border: 1px;
	ayayay: 44px;
}

EOD;
		$actual = new CM_Css($css, $this->_getRender());
		$this->assertEquals($expected, $actual->__toString());
	}

	public function testFilterProperty() {
		$css = <<<'EOD'
var {
	filter: alpha(foobar) hello(world);
	opacity: 0.3;
}
EOD;
		$expected = <<<'EOD'
var {
	filter: alpha(opacity=30) hello(world);
	opacity: 0.3;
}

EOD;
		$actual = new CM_Css($css, $this->_getRender());
		$this->assertEquals($expected, $actual->__toString());
	}

	public function testToString() {
		$presets = <<<'EOD'
$var1 {
	width: 50%;
	height: 50%;	
}
$var2 {
	width: 60%;
}
$var3 {
	Height: 190px;
	border: 1px solid lightGrey;
	overwriteparam: its overwritten;
}
EOD;
		$css = <<<'EOD'
.test:visible {
	color: black;
	height:300px;
}
.test2 << $var1, $var2  {
Height:300px;
}
.test3 .whale, .test4 .whale << $var3, $var1 {
	testparamb: testParam3;
	overwriteparam: #FFFFFF;
}
{
	testparama: testValue1;
	testparamb: testValue2;
}
EOD;
		$expected = <<<'EOD'
.cmp-test .test:visible {
	color: black;
	height: 300px;
}
.cmp-test .test2 {
	width: 60%;
	height: 300px;
}
.cmp-test .test3 .whale, .cmp-test .test4 .whale {
	height: 50%;
	border: 1px solid lightGrey;
	overwriteparam: #FFFFFF;
	width: 50%;
	testparamb: testParam3;
}
.cmp-test {
	testparama: testValue1;
	testparamb: testValue2;
}

EOD;
		$actual = new CM_Css($css, $this->_getRender(), new CM_Css($presets, $this->_getRender()), '.cmp-test');
		$this->assertEquals($expected, $actual->__toString());
	}

	public function testAttribute() {
		$css = <<<'EOD'
input[attr="val"] {
	border: 1px;
}
EOD;
		$expected = <<<'EOD'
input[attr="val"] {
	border: 1px;
}

EOD;
		$actual = new CM_Css($css, $this->_getRender());
		$this->assertEquals($expected, $actual->__toString());
	}

	public function testAt() {
		$css = <<<'EOD'
@ {
	border: 1px;
}
EOD;
		$expected = <<<'EOD'
.prefix {
	border: 1px;
}

EOD;
		$actual = new CM_Css($css, $this->_getRender(), null, '.prefix');
		$this->assertEquals($expected, $actual->__toString());
	}

	public function testLinearGradient() {
		$css = <<<'EOD'
.foo {
	background-image: linear-gradient(left, #000000, rgba(30, 50,30, 0.4));
}
EOD;
		$expected = <<<'EOD'
.foo {
	filter: progid:DXImageTransform.Microsoft.gradient(GradientType=1,startColorstr=#000000,endColorstr=#661e321e);
	background-image: linear-gradient(left,#000000,rgba(30,50,30,0.4));
	background-image: -moz-linear-gradient(left,#000000,rgba(30,50,30,0.4));
	background-image: -webkit-linear-gradient(left,#000000,rgba(30,50,30,0.4));
	background-image: -o-linear-gradient(left,#000000,rgba(30,50,30,0.4));
	background-image: -webkit-gradient(linear,left top,right top,from(#000000),to(rgba(30,50,30,0.4)));
}

EOD;
		$actual = new CM_Css($css, $this->_getRender());
		$this->assertEquals($expected, $actual->__toString());
	}

	public function testLinearGradientNoMatch() {
		$css = <<<'EOD'
.foo {
	background-image: -foo-linear-gradient(left, #000000, #ffffff);
}
EOD;
		$expected = <<<'EOD'
.foo {
	background-image: -foo-linear-gradient(left, #000000, #ffffff);
}

EOD;
		$actual = new CM_Css($css, $this->_getRender());
		$this->assertEquals($expected, $actual->__toString());
	}

	private function _getRender() {
		return CM_Render::getInstance();
	}
}
