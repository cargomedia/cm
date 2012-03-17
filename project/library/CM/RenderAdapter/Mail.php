<?php

class CM_RenderAdapter_Mail extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Mail $mail */
		$mail = $this->_getRenderable();
		if ($mail->getRenderLayout() || $mail->hasTemplate()) {
			$this->getLayout()->assign($mail->getTplParams());
		}
		if (!($subject = $mail->getSubject())) {
			if (!$mail->hasTemplate()) {
				throw new CM_Exception_Invalid('Trying to render mail with neither subject nor template');
			}
			$subject = $this->getLayout()->fetch($this->_getTplPath('subject.tpl'));
			$subject = trim($subject);
		}
		if (!($htmlBody = $mail->getHtml()) && $mail->hasTemplate()) {
			$htmlBody = $this->getLayout()->fetch($this->_getTplPath('body.tpl'));
		}
		if ($mail->getRenderLayout()) {
			$this->getLayout()->assign('subject', $subject);
			$this->getLayout()->assign('body', $htmlBody);
			$html = $this->getLayout()->fetch($this->getRender()->getLayoutPath('layout/mailHtml.tpl'));
		} else {
			$html = $htmlBody;
		}
		if (!($text = $mail->getText())) {
			if (!$htmlBody) {
				throw new CM_Exception_Invalid('Mail has neither text nor html content');
			}
			$text = preg_replace('!\n!', ' ', $htmlBody);
			$text = preg_replace(array('!<br\s*/?>!', '!<a .*?href="(.*?)".*?>(.*?)</a>!', '!</?p>!'), array("\n", '$2 ($1)', "\n"), $text);
			$text = preg_replace('!(\n)\s+!', "\n", $text);
			$text = trim(strip_tags($text));
		}
		if ($mail->getRenderLayout()) {
			$this->getLayout()->assign('body', $text);
			$text = $this->getLayout()->fetch($this->getRender()->getLayoutPath('layout/mailText.tpl'));
		}
		return array($subject, $html, $text);
	}
}
