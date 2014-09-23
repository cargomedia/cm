<?php

class CM_Mail_WelcomeTest extends CMTest_TestCase {

    public function testRenderTranslated() {
        $recipient = CMTest_TH::createUser();
        $mail = new CM_Mail_Welcome($recipient);
        $language = CM_Model_Language::create('Test language', 'foo', true);
        $language->setTranslation('Welcome to {$siteName}!', 'foo');

        list($subject, $html, $text) = $mail->render();
        $nodeList = new CM_Dom_NodeList(htmlspecialchars($html));

        $this->assertContains('foo', $nodeList->getText());
    }
}
