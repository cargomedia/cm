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

    public function testLinearGradient() {
        $render = new CM_Frontend_Render();
        //horizontal
        $css = <<<'EOD'
.foo {
	.gradient(horizontal, #000000, rgba(30, 50,30, 0.4), 15%);
}
EOD;
        $expected = <<<'EOD'
.foo {
  filter: progid:DXImageTransform.Microsoft.gradient(GradientType=1,startColorstr=#ff000000,endColorstr=#661e321e);
  background-image: -webkit-linear-gradient(left, #000000 15%, rgba(30,50,30,0.4) 100%);
  background-image: linear-gradient(to right,#000000 15%,rgba(30,50,30,0.4) 100%);
}

EOD;
        $css = new CM_Asset_Css($render, $css, [
            'autoprefixerBrowsers' => 'Safari 6',
        ]);
        $this->assertSame(trim($expected), $css->get());

        //vertical
        $css = <<<'EOD'
.foo {
	.gradient(vertical, #000000, rgba(30, 50,30, 0.4));
}
EOD;
        $expected = <<<'EOD'
.foo {
  filter: progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=#ff000000,endColorstr=#661e321e);
  background-image: -webkit-linear-gradient(top, #000000 0%, rgba(30,50,30,0.4) 100%);
  background-image: linear-gradient(to bottom,#000000 0%,rgba(30,50,30,0.4) 100%);
}

EOD;
        $css = new CM_Asset_Css($render, $css, [
            'autoprefixerBrowsers' => 'Safari 6',
        ]);
        $this->assertSame(trim($expected), $css->get());

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
        $css = new CM_Asset_Css($render, $css);
        $this->assertSame('', $css->get());
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
	transition: transform 1s;
}
EOD;
        $expected = '.foo{-webkit-transition:-webkit-transform 1s;transition:transform 1s;}';
        $css = new CM_Asset_Css($render, $css, [
            'autoprefixerBrowsers' => 'Safari 6',
        ]);
        $this->assertSame(trim($expected), $css->get(true));
    }
}
