<?php

class CM_Frontend_TemplateHelper_ContentPlaceholder {

    /**
     * @param string    $content
     * @param int|null  $width
     * @param int|null  $height
     * @param bool|null $stretch
     * @return string
     */
    public static function create($content, $width = null, $height = null, $stretch = null) {
        if (null === $width) {
            $width = 1;
        }
        if (null === $height) {
            $height = 1;
        }
        $stretch = (bool) $stretch;
        $imageData = self::_createBlankImage($width, $height);
        $imageSrc = 'data:image/png;base64,' . base64_encode($imageData);

        $output = '<div class="contentPlaceholder' . ($stretch ? ' stretch' : '') . '">';
        $output .= '<img class="contentPlaceholder-size" src="' . $imageSrc . '"/>';
        $output .= $content;

        $output .= '</div>';
        return $output;
    }

    /**
     * @param int $width
     * @param int $height
     * @return string
     */
    private static function _createBlankImage($width, $height) {
        $cache = CM_Cache_Local::getInstance();
        return $cache->get($cache->key(CM_CacheConst::Frontend_TemplateHelper_ContentPlaceholder, $width, $height), function () use ($width, $height) {
            $image = imagecreate($width, $height);
            ob_start();
            imagecolorallocate($image, 255, 255, 255);
            imagepng($image);
            $imageData = ob_get_contents();
            ob_end_clean();
            return $imageData;
        });
    }
}
