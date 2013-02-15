<?php

class CM_Markdown extends Michelf\Markdown {

	function formParagraphs($text) {

		$text = preg_replace('/\A\n+|\n+\z/', '', $text);
		$grafs = preg_split('/\n{1,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

		foreach ($grafs as $key => $value) {
			if (!preg_match('/^B\x1A[0-9]+B$/', $value)) {
				# Is a paragraph.
				$value = $this->runSpanGamut($value);
				$value = preg_replace('/^([ ]*)/', "<p>", $value);
				$value .= "</p>";
				$grafs[$key] = $this->unhash($value);
			} else {
				# Is a block.
				# Modify elements of @grafs in-place...
				$graf = $value;
				$block = $this->html_hashes[$graf];
				$graf = $block;
				$grafs[$key] = $graf;
			}
		}
		return implode("\n\n", $grafs);
	}
}
