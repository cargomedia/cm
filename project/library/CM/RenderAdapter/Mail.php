<?php

class CM_RenderAdapter_Mail extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Mail $mail */
		$mail = $this->_getView();
		if (!($subject = $mail->getSubject())) {
			if (!$mail->hasTemplate()) {
				throw new CM_Exception_Invalid('Trying to render mail with neither subject nor template');
			}
			$subject = $this->_renderTemplate('subject.tpl', $mail->getTplParams());
			$subject = trim($subject);
		}
		if (!($htmlBody = $mail->getHtml()) && $mail->hasTemplate()) {
			$htmlBody = $this->_renderTemplate('body.tpl', $mail->getTplParams());
		}
		if ($mail->getRenderLayout()) {
			$tplPath = $this->getRender()->getLayoutPath('mailLayout/mailHtml.tpl');
			$assign = array_merge($mail->getTplParams(), array('subject' => $subject, 'body' => $htmlBody));
			$html = $this->getRender()->renderTemplate($tplPath, $assign);
		} else {
			$html = $htmlBody;
		}
		if ($mail->getRecipient()) {
			$imageTag = '<img style="display: none; width: 0px; height: 0px;" src="' . $this->getRender()->getUrlEmailTracking($mail) . '" />';
			$html = preg_replace('#</body>#', $imageTag . '$0', $html);
		}

		if (!($text = $mail->getText())) {
			if (!$htmlBody) {
				throw new CM_Exception_Invalid('Mail has neither text nor html content');
			}
			$text = $htmlBody;
			$text = preg_replace('#\n#', ' ', $text);
			$text = preg_replace('#<br\s*/?>#', "\n", $text);
			$text = preg_replace('#</?p>#', "\n", $text);
			$text = preg_replace('#<a .*?href="(.+?)".*?>(.+?)</a>#s', '$2 ($1)', $text);
			$text = preg_replace('#<li.*?>\s*(.+?)</li>#s', '* $1', $text);
			$text = strip_tags($text);
			$text = preg_replace('#(\n)\s+#', '$1', $text);
			$text = preg_replace('#([ \t])[ \t]+#', '$1', $text);
			$text = trim($text);
		}
		if ($mail->getRenderLayout()) {
			$tplPath = $this->getRender()->getLayoutPath('mailLayout/mailText.tpl');
			$assign = array_merge($mail->getTplParams(), array('subject' => $subject, 'body' => $text));
			$text = $this->getRender()->renderTemplate($tplPath, $assign);
		}
		return array($subject, $html, $text);
	}
}
