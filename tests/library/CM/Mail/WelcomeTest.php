<?php

class CM_Mail_WelcomeTest extends CMTest_TestCase {

    public function testRenderTranslated() {
        $site = $this->getMockSite(null, null, [
            'url' => 'http://www.foo.com',
        ]);
        $recipient = $this->getMockUser(null, $site);
        $mail = new CM_Mail_ExampleMailable($recipient);
        $language = CM_Model_Language::create('Test language', 'foo', true);
        $language->setTranslation('Welcome to {$siteName}!', 'foo');

        list($subject, $html, $text) = $mail->render();
        $nodeList = new CM_Dom_NodeList($html);

        $this->assertContains('foo', $nodeList->getText());

        $nodeLink = $nodeList->find('a');
        $this->assertSame(1, $nodeLink->count());
        $this->assertSame('http://www.foo.com/example', $nodeLink->getAttribute('href'));
        $this->assertSame('Example Page', $nodeLink->getText());
        $this->assertContains('border-style:solid;', $nodeLink->getAttribute('style'));
    }
}
