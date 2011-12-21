<?php

class CM_RenderAdapter_Mail extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Mail $mail */
		$mail = $this->_getObject();
		if ($mail->getRenderLayout() || $mail->hasTemplate()) {
			$this->getLayout()->assign($mail->getTplParams());
		}
		if (!($subject = $mail->getSubject())) {
			if ($mail->getDemoMode()) {
				$subject = file_get_contents($mail->getSubjectTplPath());
			} else {
				$subject = $this->getLayout()->fetch($mail->getSubjectTplPath());
			}
			$subject = trim($subject);
		}
		if (!($htmlBody = $mail->getHtml()) && $mail->hasTemplate()) {
			if ($mail->getDemoMode()) {
				$htmlBody = file_get_contents($mail->getTplPath());
			} else {
				$htmlBody = $this->getLayout()->fetch($mail->getTplPath());
			}
		}
		if ($mail->getRenderLayout()) {
			$this->getLayout()->assign('subject', $subject);
			$this->getLayout()->assign('body', $htmlBody);
			$html = $this->getLayout()->fetch($mail->getHtmlLayoutTplPath());
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
			$text = $this->getLayout()->fetch($mail->getTextLayoutTplPath());
		}
		return array($subject, $html, $text);
	}
}
