<?php

class CM_Asset_CssTest extends CMTest_TestCase {

    public function testAdd() {
        $render = new CM_Frontend_Render();
        $css = new CM_Asset_Css($render, 'font-size: 12;', [
            'prefix' => '#foo',
        ]);
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
  font-size: 12;
  color: green;
}
#foo .bar .test:visible {
  color: black;
  height: 300px;
}
EOD;
        $this->assertSame(trim($expected), $css->get());
    }

    public function testImage() {
        $render = new CM_Frontend_Render();
        $css = new CM_Asset_Css($render, "body { background: image('icon/mailbox_read.png') no-repeat 66px 7px; }");
        $url = $render->getUrlResource('layout', 'img/icon/mailbox_read.png');
        $expected = <<<EOD
body {
  background: url('$url') no-repeat 66px 7px;
}
EOD;
        $this->assertEquals(trim($expected), $css->get());
    }

    public function testImageInline() {
        $render = new CM_Frontend_Render();
        $css = new CM_Asset_Css($render, "body { background: image-inline('select2/select2.png') no-repeat 60px 40px; }");

        $encodedImage = 'R0lGODlhPAAoAPEAAEZGRoiIiAAAAAAAACH5BAEAAAIALAAAAAA8ACgAAAJ5lI+py+0Po5y02otxQDt7F3Rh95XcGJrqma4u6q5oG3/t+JJ1MuugvzvAI' .
            'sMgj/aYGY+SXgaAgC4vAGlVOqVcr1kqt2vZgivb6nhiFnzP7Lb7DY/L5/RMsS7A4fB5HVL+l4cXCAT3FxiHhJjYU8i4ccfXt1hHSRdYAAA7';

        $expected = <<<EOD
body {
  background: url('data:image/gif;base64,$encodedImage') no-repeat 60px 40px;
}
EOD;
        $this->assertEquals(trim($expected), $css->get());
    }

    public function testImageInlineResize() {
        $render = new CM_Frontend_Render();
        $css = new CM_Asset_Css($render, "body { background: image-inline('select2/select2.png', 20) no-repeat 20px 20px; }");

        $encodedImage = 'R0lGODlhFAANAPEAAEZGRoiIiAAAAAAAACH5BAEAAAIALAAAAAAUAA0AAAIalI+pyxINwzMTrkqtTgBs7n3iGJGOSWHjVAAAOw==';

        $expected = <<<EOD
body {
  background: url('data:image/gif;base64,$encodedImage') no-repeat 20px 20px;
}
EOD;
        $this->assertEquals(trim($expected), $css->get());
    }

    public function testBackgroundImage() {
        $render = new CM_Frontend_Render();
        $css = new CM_Asset_Css($render, "body { background-image: image('icon/mailbox_read.png'); }");
        $url = $render->getUrlResource('layout', 'img/icon/mailbox_read.png');
        $expected = <<<EOD
body {
  background-image: url('$url');
}
EOD;
        $this->assertEquals($expected, $css->get());
    }

    public function testUrlFont() {
        $render = new CM_Frontend_Render();
        $css = new CM_Asset_Css($render, "body { src: url(urlFont('file.eot')); }");
        $url = $render->getUrlStatic('/font/file.eot');
        $expected = <<<EOD
body {
  src: url('$url');
}
EOD;
        $this->assertEquals($expected, $css->get());
    }

    public function testMixin() {
        $render = new CM_Frontend_Render();
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
        $css = new CM_Asset_Css($render, $css);
        $expected = <<<'EOD'
.foo {
  color: red;
  font-size: 5;
  border: 1px solid red;
}
.foo #bar {
  color: blue;
}
EOD;
        $this->assertEquals(trim($expected), $css->get());
    }

    public function testAutoprefixer() {
        $render = new CM_Frontend_Render();
        $css = <<<'EOD'
.foo {
	transition: transform 1s;
}
EOD;
        $expected = <<<'EOD'
.foo {
  -webkit-transition: -webkit-transform 1s;
          transition: transform 1s;
}
EOD;
        $css = new CM_Asset_Css($render, $css, [
            'autoprefixerBrowsers' => 'Android 4.3',
        ]);
        $this->assertSame(trim($expected), $css->get());
    }

    public function testMedia() {
        $render = new CM_Frontend_Render();
        $css = <<<'EOD'
.foo {
	color: blue;
	@media (max-width : 767px) {
		color: red;
	}
}
EOD;
        $expected = <<<'EOD'
.foo {
  color: blue;
}
@media (max-width: 767px) {
  .foo {
    color: red;
  }
}

EOD;
        $css = new CM_Asset_Css($render, $css);
        $this->assertSame(trim($expected), $css->get());
    }

    public function testCompress() {
        $render = new CM_Frontend_Render();
        $css = <<<'EOD'
.foo {
	color: red;
}
EOD;
        $expected = '.foo{color:red;}';
        $css = new CM_Asset_Css($render, $css, [
            'autoprefixerBrowsers' => 'Chrome 30',
        ]);
        $this->assertSame(trim($expected), $css->get(true));
    }
}
