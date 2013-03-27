<?php

class CM_Usertext_Filter_Emoticon_EscapeMarkdown implements CM_Usertext_Filter_Interface {

	public function transform($text, CM_Render $render) {
		$text = (string) $text;
		$text = preg_replace('#:[[:alnum:]_]{1,50}:#ue', 'str_replace("_", "-", \'$0\')', $text);
		return $text;
	}

}
