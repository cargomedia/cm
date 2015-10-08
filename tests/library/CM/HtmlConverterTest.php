<?php

class CM_HtmlConverterTest extends CMTest_TestCase {

    public function testConvertHtmlToPlainText() {
        $suites = [
            'Chrome, Firefox, Safari, iOS, Android' => [
                'foo<br />&nbsp; bar<br /><br />' => "foo\n  bar\n\n",
            ],
            'Internet Explorer 11'                  => [
                '<p>foo<br />&nbsp; bar</p><p><br /></p>' => "foo\n  bar\n\n",
            ],
            'Internet Explorer Edge'                => [
                '<div>foo<br />&nbsp; bar</div><div></div>' => "foo\n  bar\n\n",
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
