<?php

class CM_Frontend_TemplateHelper_RatioKeeper {

    /**
     * @param string $content
     * @param int    $width
     * @param int    $height
     * @param bool   $stretch
     * @return string
     */
    public static function create($content, $width = 1, $height = 1, $stretch = false) {
        $imageData = self::_createBlankImage($width, $height);
        $imageSrc = 'data:image/png;base64,' . base64_encode($imageData);

        $output = '<div class="ratioKeeper' . ($stretch ? ' stretch' : '') . '">';
        $output .= '<img class="ratioKeeper-size" src="' . $imageSrc . '">';
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
        return $cache->get($cache->key(CM_CacheConst::Frontend_TemplateHelper_RatioKeeper, $width, $height), function () use ($width, $height) {
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
