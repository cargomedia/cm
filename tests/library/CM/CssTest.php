<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_CssTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testToString() {
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
		$this->assertEquals($expected, (string) $css);
	}
}
