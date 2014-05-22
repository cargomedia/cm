<?php

class CM_Usertext_Filter_Markdown_UserContent implements CM_Usertext_Filter_Interface
{
    public function transform($text, CM_Render $render)
    {
        $text = (string)$text;
        $text = preg_replace_callback('#!\[usercontent\]\(([^\]]+)\)#m', function ($matches) {
            $relativeUrl = $matches[1];
            $userFile = new CM_File_UserContent('usercontent', $relativeUrl);
            return '<img src="' . $userFile->getUrl() . '" alt="image"/>';
        }, $text);

        return $text;
    }
}
