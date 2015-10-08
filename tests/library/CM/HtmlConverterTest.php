<?php

class CM_HtmlConverterTest extends CMTest_TestCase {

    public function testConvertHtmlToPlainText() {
        $suites = [
            'Chrome, Firefox, Safari, iOS, Android' => [
                'foo<br />&nbsp; &lt;div&gt;bar&lt;/div&gt;<br /><br />' => "foo\n  <div>bar</div>\n\n",
            ],
            'Internet Explorer 11'                  => [
                '<p>foo<br />&nbsp; &lt;div&gt;bar&lt;/div&gt;</p><p><br /></p>' => "foo\n  <div>bar</div>\n\n",
            ],
            'Internet Explorer Edge'                => [
                '<div>foo<br />&nbsp; &lt;div&gt;bar&lt;/div&gt;</div><div></div>' => "foo\n  <div>bar</div>\n\n",
            ],
        ];

        $converter = new CM_HtmlConverter();
        foreach ($suites as $suiteName => $cases) {
            foreach ($cases as $html => $expectedPlainText) {
                $message = "Conversion failed for `{$suiteName}` for `{$html}`";
                $this->assertSame($expectedPlainText, $converter->convertHtmlToPlainText($html), $message);
            }
        }
    }
}
