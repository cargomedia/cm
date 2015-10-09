<?php

class CM_HtmlConverter {

    /**
     * @param string $html
     * @return string
     */
    public function convertHtmlToPlainText($html) {
        $html = preg_replace('/<div><br(?:\s\/)?><\/div>/', PHP_EOL, $html); // IE Edge empty line
        $html = preg_replace('/<p><br(?:\s\/)?><\/p>/', PHP_EOL, $html);     // IE 11 empty line
        $html = preg_replace('/<br(?:\s\/)?>/', PHP_EOL, $html);             // Chrome, Firefox, Safari enter and shift+enter
        $html = preg_replace('/<div>/', '', $html);                          // remove opening div for IE Edge enter
        $html = preg_replace('/<\/div>/', PHP_EOL, $html);                   // replace closing div for IE Edge enter
        $html = preg_replace('/<p>/', '', $html);                            // remove opening p for IE 11 enter
        $html = preg_replace('/<\/p>/', PHP_EOL, $html);                     // replace closing p for IE 11 enter
        $html = preg_replace('/&nbsp;/', ' ', $html);                        // non-breaking space
        $html = strip_tags($html);
        $html = html_entity_decode($html, null, 'UTF-8');
        return $html;
    }
}
